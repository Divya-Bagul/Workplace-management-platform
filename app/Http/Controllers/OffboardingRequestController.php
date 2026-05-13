<?php

namespace App\Http\Controllers;

use App\Events\OffboardingStatusUpdated;
use App\Http\Requests\StoreOffboardingRequestRequest;
use App\Models\Employee;
use App\Models\OffboardingRequest;
use App\Notifications\OffboardingInitiatedForIt;
use App\Services\AuditLogger;
use App\Services\DeskAssignmentService;
use App\Services\WorkplaceNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OffboardingRequestController extends Controller
{
    public function __construct(
        private readonly DeskAssignmentService $desks,
        private readonly WorkplaceNotifier $notifier,
    ) {}

    public function index(): View
    {
        $requests = OffboardingRequest::query()
            ->with(['employee', 'initiator'])
            ->latest()
            ->get();

        return view('hr.offboarding.index', compact('requests'));
    }

    public function create(): View
    {
        $employees = Employee::query()
            ->where('employment_status', 'active')
            ->whereDoesntHave('offboardingRequests', fn ($q) => $q->whereNotIn('status', ['completed', 'cancelled']))
            ->orderBy('name')
            ->get();

        return view('hr.offboarding.create', compact('employees'));
    }

    public function store(StoreOffboardingRequestRequest $request): RedirectResponse
    {
        $employee = Employee::query()->findOrFail($request->integer('employee_id'));

        $open = OffboardingRequest::query()
            ->where('employee_id', $employee->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->exists();

        if ($open) {
            return back()->withErrors(['employee_id' => __('This employee already has an open offboarding request.')]);
        }

        $offboarding = OffboardingRequest::query()->create([
            'employee_id' => $employee->id,
            'initiated_by' => $request->user()->id,
            'last_working_day' => $request->date('last_working_day'),
            'status' => 'pending',
            'hr_notes' => $request->input('hr_notes'),
        ]);

        AuditLogger::log($request->user()->id, 'offboarding.created', $offboarding, null, $offboarding->toArray());

        $this->notifier->notifyUsersWithRoles(['it', 'admin'], new OffboardingInitiatedForIt($offboarding));
        event(new OffboardingStatusUpdated($offboarding->load('employee')));

        return redirect()->route('offboarding.show', $offboarding)->with('status', __('Offboarding initiated. IT has been notified (queued).'));
    }

    public function show(OffboardingRequest $offboarding): View
    {
        $offboarding->load(['employee.department', 'initiator']);

        return view('hr.offboarding.show', compact('offboarding'));
    }

    public function startRecovery(Request $request, OffboardingRequest $offboarding): RedirectResponse
    {
        if ($offboarding->status !== 'pending') {
            return back()->withErrors(['status' => __('Recovery can only start from pending.')]);
        }

        $before = $offboarding->toArray();
        $offboarding->update([
            'status' => 'recovery_in_progress',
            'assets_recovery_started_at' => now(),
        ]);

        AuditLogger::log($request->user()->id, 'offboarding.recovery_started', $offboarding, $before, $offboarding->toArray());
        event(new OffboardingStatusUpdated($offboarding->load('employee')));

        return back()->with('status', __('Asset recovery workflow started.'));
    }

    public function markAssetsRecovered(Request $request, OffboardingRequest $offboarding): RedirectResponse
    {
        if (! in_array($offboarding->status, ['pending', 'recovery_in_progress'], true)) {
            return back()->withErrors(['status' => __('Invalid status for this step.')]);
        }

        $before = $offboarding->toArray();
        $offboarding->update([
            'status' => 'assets_recovered',
            'assets_recovered_at' => now(),
        ]);

        AuditLogger::log($request->user()->id, 'offboarding.assets_recovered', $offboarding, $before, $offboarding->toArray());
        event(new OffboardingStatusUpdated($offboarding->load('employee')));

        return back()->with('status', __('Assets marked as recovered / returned.'));
    }

    public function releaseDesk(Request $request, OffboardingRequest $offboarding): RedirectResponse
    {
        if ($offboarding->status !== 'assets_recovered') {
            return back()->withErrors(['status' => __('Mark assets as recovered before releasing the desk.')]);
        }

        $employee = $offboarding->employee;
        $before = $offboarding->toArray();

        DB::transaction(function () use ($offboarding, $employee) {
            $this->desks->releaseDeskForEmployee($employee, Carbon::today());
            $offboarding->update([
                'status' => 'desk_released',
                'desk_released_at' => now(),
            ]);
        });

        $offboarding->refresh();
        AuditLogger::log($request->user()->id, 'offboarding.desk_released', $offboarding, $before, $offboarding->toArray());
        event(new OffboardingStatusUpdated($offboarding->load('employee')));

        return back()->with('status', __('Desk released after clearance.'));
    }

    public function complete(Request $request, OffboardingRequest $offboarding): RedirectResponse
    {
        if ($offboarding->status !== 'desk_released') {
            return back()->withErrors(['status' => __('Release the desk before completing offboarding.')]);
        }

        $before = $offboarding->toArray();

        DB::transaction(function () use ($offboarding) {
            $offboarding->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
            $offboarding->employee?->update(['employment_status' => 'inactive']);
        });

        $offboarding->refresh();
        AuditLogger::log($request->user()->id, 'offboarding.completed', $offboarding, $before, $offboarding->toArray());
        event(new OffboardingStatusUpdated($offboarding->load('employee')));

        return back()->with('status', __('Offboarding completed. Employee marked inactive.'));
    }

    public function cancel(Request $request, OffboardingRequest $offboarding): RedirectResponse
    {
        if (in_array($offboarding->status, ['completed', 'cancelled'], true)) {
            return back()->withErrors(['status' => __('Request is already closed.')]);
        }

        $before = $offboarding->toArray();
        $offboarding->update(['status' => 'cancelled']);

        AuditLogger::log($request->user()->id, 'offboarding.cancelled', $offboarding, $before, $offboarding->toArray());
        event(new OffboardingStatusUpdated($offboarding->load('employee')));

        return back()->with('status', __('Offboarding cancelled.'));
    }
}
