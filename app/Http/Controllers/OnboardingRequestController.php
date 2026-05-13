<?php

namespace App\Http\Controllers;

use App\Events\OnboardingStatusUpdated;
use App\Http\Requests\AssignDeskOnboardingRequest;
use App\Http\Requests\StoreOnboardingRequestRequest;
use App\Models\Desk;
use App\Models\DeskAllocation;
use App\Models\Employee;
use App\Models\OnboardingRequest;
use App\Notifications\OnboardingForwardedToIt;
use App\Notifications\OnboardingStatusChanged;
use App\Services\AuditLogger;
use App\Services\DeskAssignmentService;
use App\Services\WorkplaceNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class OnboardingRequestController extends Controller
{
    public function __construct(
        private readonly DeskAssignmentService $desks,
        private readonly WorkplaceNotifier $notifier,
    ) {}

    public function index(Request $request): View
    {
        $query = OnboardingRequest::query()
            ->with([
                'employee.assetAssignments.asset.assetType',
                'author',
                'desk.floor.building',
            ]);

        if ($this->isItOnlyUser($request)) {
            $query->whereIn('status', OnboardingRequest::IT_QUEUE_STATUSES);
        }

        $requests = $query->latest()->get();

        return view('hr.onboarding.index', compact('requests'));
    }

    public function create(): View
    {
        $employees = Employee::query()
            ->where('employment_status', 'active')
            ->whereDoesntHave('onboardingRequests', fn ($q) => $q->whereNotIn('status', ['completed', 'cancelled']))
            ->orderBy('name')
            ->get();

        $desks = Desk::query()->with('floor.building')->orderBy('floor_id')->orderBy('code')->get();

        return view('hr.onboarding.create', compact('employees', 'desks'));
    }

    public function store(StoreOnboardingRequestRequest $request): RedirectResponse
    {
        $employee = Employee::query()->findOrFail($request->integer('employee_id'));

        $open = OnboardingRequest::query()
            ->where('employee_id', $employee->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->exists();

        if ($open) {
            return back()->withErrors(['employee_id' => __('This employee already has an open onboarding request.')]);
        }

        try {
            $onboarding = DB::transaction(function () use ($request, $employee) {
                $onboarding = OnboardingRequest::query()->create([
                    'employee_id' => $employee->id,
                    'created_by' => $request->user()->id,
                    'status' => 'draft',
                    'desk_id' => $request->input('desk_id'),
                    'hr_notes' => $request->input('hr_notes'),
                ]);

                if ($onboarding->desk_id) {
                    $desk = Desk::query()->findOrFail($onboarding->desk_id);
                    $blocked = DeskAllocation::query()
                        ->where('desk_id', $desk->id)
                        ->whereNull('valid_to')
                        ->where('employee_id', '!=', $employee->id)
                        ->exists();
                    if ($blocked) {
                        throw new \RuntimeException('desk_occupied');
                    }
                    $validFrom = Carbon::parse($employee->joining_date ?? Carbon::today()->toDateString());
                    $this->desks->assignDeskToEmployee($employee, $desk, $validFrom, 'Onboarding draft desk assignment');
                    $onboarding->update([
                        'status' => 'desk_assigned',
                        'desk_assigned_at' => now(),
                    ]);
                }

                return $onboarding;
            });
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'desk_occupied') {
                return back()->withErrors(['desk_id' => __('Selected desk is already occupied by another employee.')]);
            }
            throw $e;
        }

        AuditLogger::log($request->user()->id, 'onboarding.created', $onboarding, null, $onboarding->toArray());
        event(new OnboardingStatusUpdated($onboarding->fresh(['employee', 'desk'])));

        return $this->redirectToOnboardingWorkflow(
            $onboarding,
            $onboarding->desk_id
                ? __('Onboarding request created. Desk assigned — review the flow below, then forward this request to IT.')
                : __('Onboarding request created.')
        );
    }

    public function show(Request $request, OnboardingRequest $onboarding): View
    {
        if ($this->isItOnlyUser($request) && ! $onboarding->isVisibleToIt()) {
            throw new AccessDeniedHttpException(__('This onboarding request is not available for IT.'));
        }

        $onboarding->load([
            'employee.department',
            'employee.building',
            'employee.floor',
            'employee.assetAssignments.asset.assetType',
            'author',
            'desk.floor.building',
        ]);

        $desks = Desk::query()->with('floor.building')->orderBy('floor_id')->orderBy('code')->get();

        return view('hr.onboarding.show', compact('onboarding', 'desks'));
    }

    public function assignDesk(AssignDeskOnboardingRequest $request, OnboardingRequest $onboarding): RedirectResponse
    {
        if (! $onboarding->allowsHrDeskSetup()) {
            return back()->withErrors(['desk_id' => __('Desk can no longer be changed after the request is with IT.')]);
        }

        if (in_array($onboarding->status, ['completed', 'cancelled'], true)) {
            return back()->withErrors(['desk_id' => __('This onboarding request is closed.')]);
        }

        $employee = $onboarding->employee;
        $desk = Desk::query()->findOrFail($request->integer('desk_id'));

        if (! in_array($desk->status, ['available', 'reserved', 'occupied'], true)) {
            return back()->withErrors(['desk_id' => __('Desk is not assignable.')]);
        }

        $blocked = DeskAllocation::query()
            ->where('desk_id', $desk->id)
            ->whereNull('valid_to')
            ->where('employee_id', '!=', $employee->id)
            ->exists();
        if ($blocked) {
            return back()->withErrors(['desk_id' => __('Desk is already occupied by another employee.')]);
        }

        $before = $onboarding->toArray();

        DB::transaction(function () use ($onboarding, $employee, $desk) {
            $validFrom = Carbon::parse($employee->joining_date ?? Carbon::today()->toDateString());
            $this->desks->assignDeskToEmployee($employee, $desk, $validFrom, 'Onboarding desk assignment');
            $onboarding->update([
                'desk_id' => $desk->id,
                'desk_assigned_at' => now(),
                'status' => 'desk_assigned',
            ]);
        });

        $onboarding->refresh();
        AuditLogger::log($request->user()->id, 'onboarding.desk_assigned', $onboarding, $before, $onboarding->toArray());

        $this->notifier->notifyUsersWithRoles(
            ['it', 'admin'],
            new OnboardingStatusChanged($onboarding, 'Desk assigned for new hire')
        );

        event(new OnboardingStatusUpdated($onboarding->load(['employee', 'desk'])));

        return $this->redirectToOnboardingWorkflow(
            $onboarding,
            __('Desk assigned. Review the flow below, then forward this request to IT.')
        );
    }

    public function forwardToIt(Request $request, OnboardingRequest $onboarding): RedirectResponse
    {
        $request->validate([
            'it_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        if (! $onboarding->desk_id) {
            return back()->withErrors(['desk' => __('Assign a desk before forwarding to IT.')]);
        }

        if (! $onboarding->allowsHrDeskSetup()) {
            return back()->withErrors(['status' => __('This request has already been forwarded to IT.')]);
        }

        if (in_array($onboarding->status, ['completed', 'cancelled'], true)) {
            return back()->withErrors(['status' => __('This onboarding request is closed.')]);
        }

        $before = $onboarding->toArray();

        $onboarding->update([
            'status' => 'it_pending',
            'submitted_to_it_at' => now(),
            'it_notes' => $request->input('it_notes', $onboarding->it_notes),
        ]);

        AuditLogger::log($request->user()->id, 'onboarding.forwarded_it', $onboarding, $before, $onboarding->toArray());

        $this->notifier->notifyUsersWithRoles(['it', 'admin'], new OnboardingForwardedToIt($onboarding));
        event(new OnboardingStatusUpdated($onboarding->load(['employee', 'desk'])));

        return redirect()->route('onboarding.show', $onboarding)->with('status', __('Request queued for IT (email + in-app) and broadcast to connected clients.'));
    }

    public function itSetupStart(Request $request, OnboardingRequest $onboarding): RedirectResponse
    {
        $this->ensureItCanManage($request, $onboarding);

        if ($onboarding->status !== 'it_pending') {
            return back()->withErrors(['status' => __('IT can only start after the request is forwarded to IT.')]);
        }

        $before = $onboarding->toArray();
        $onboarding->update([
            'status' => 'it_in_progress',
            'it_setup_started_at' => now(),
        ]);

        AuditLogger::log($request->user()->id, 'onboarding.it_started', $onboarding, $before, $onboarding->toArray());
        $this->notifier->notifyUsersWithRoles(
            ['hr', 'admin'],
            new OnboardingStatusChanged($onboarding, 'IT setup started')
        );
        event(new OnboardingStatusUpdated($onboarding->load(['employee', 'desk'])));

        return redirect()->route('onboarding.show', $onboarding)->with('status', __('IT setup marked as in progress.'));
    }

    public function itSetupComplete(Request $request, OnboardingRequest $onboarding): RedirectResponse
    {
        $this->ensureItCanManage($request, $onboarding);

        $request->validate([
            'it_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        if (! in_array($onboarding->status, ['it_pending', 'it_in_progress'], true)) {
            return back()->withErrors(['status' => __('IT cannot complete setup for this status.')]);
        }

        $before = $onboarding->toArray();
        $onboarding->update([
            'status' => 'it_complete',
            'it_setup_completed_at' => now(),
            'it_notes' => $request->input('it_notes', $onboarding->it_notes),
        ]);

        AuditLogger::log($request->user()->id, 'onboarding.it_completed', $onboarding, $before, $onboarding->toArray());
        $this->notifier->notifyUsersWithRoles(
            ['hr', 'admin'],
            new OnboardingStatusChanged($onboarding, 'IT setup completed')
        );
        event(new OnboardingStatusUpdated($onboarding->load(['employee', 'desk'])));

        return redirect()->route('onboarding.show', $onboarding)->with('status', __('IT setup marked complete.'));
    }

    public function complete(Request $request, OnboardingRequest $onboarding): RedirectResponse
    {
        if (! $onboarding->allowsHrCompletion()) {
            return back()->withErrors(['status' => __('HR can complete onboarding only after IT marks system setup complete.')]);
        }

        if ($onboarding->status !== 'it_complete') {
            return back()->withErrors(['status' => __('HR can complete onboarding only after IT marks system setup complete.')]);
        }

        if (! $onboarding->desk_id) {
            return back()->withErrors(['desk' => __('Assign a desk before completing onboarding.')]);
        }

        $before = $onboarding->toArray();
        $onboarding->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        AuditLogger::log($request->user()->id, 'onboarding.completed', $onboarding, $before, $onboarding->toArray());
        event(new OnboardingStatusUpdated($onboarding->load(['employee', 'desk'])));

        return redirect()->route('onboarding.show', $onboarding)->with('status', __('Onboarding completed.'));
    }

    public function cancel(Request $request, OnboardingRequest $onboarding): RedirectResponse
    {
        if (! $onboarding->allowsHrDeskSetup()) {
            return back()->withErrors(['status' => __('This onboarding request can no longer be cancelled from HR.')]);
        }

        if (in_array($onboarding->status, ['completed', 'cancelled'], true)) {
            return back()->withErrors(['status' => __('Request is already closed.')]);
        }

        $before = $onboarding->toArray();

        DB::transaction(function () use ($onboarding) {
            $employee = $onboarding->employee;
            if ($employee && $onboarding->desk_id) {
                $this->desks->releaseDeskForEmployee($employee, Carbon::today());
            }
            $onboarding->update(['status' => 'cancelled']);
        });

        $onboarding->refresh();
        AuditLogger::log($request->user()->id, 'onboarding.cancelled', $onboarding, $before, $onboarding->toArray());
        event(new OnboardingStatusUpdated($onboarding->load(['employee', 'desk'])));

        return redirect()->route('onboarding.show', $onboarding)->with('status', __('Onboarding cancelled and desk released if applicable.'));
    }

    private function isItOnlyUser(Request $request): bool
    {
        $user = $request->user();

        return $user?->hasRole('it') && ! $user->hasAnyRole(['admin', 'hr']);
    }

    private function ensureItCanManage(Request $request, OnboardingRequest $onboarding): void
    {
        if (! $request->user()?->hasRole('it')) {
            throw new AccessDeniedHttpException(__('Only IT can perform system setup for onboarding.'));
        }

        if (! $onboarding->isVisibleToIt()) {
            throw new AccessDeniedHttpException(__('This onboarding request is not available for IT.'));
        }
    }

    private function redirectToOnboardingWorkflow(OnboardingRequest $onboarding, string $status): RedirectResponse
    {
        return redirect()
            ->route('onboarding.show', $onboarding)
            ->withFragment('onboarding-flow')
            ->with('status', $status)
            ->with('focus_onboarding_flow', true);
    }
}
