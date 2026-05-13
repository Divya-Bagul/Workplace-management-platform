<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\OnboardingStatusUpdated;
use App\Http\Controllers\Controller;
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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OnboardingApiController extends Controller
{
    public function __construct(
        private readonly DeskAssignmentService $desks,
        private readonly WorkplaceNotifier $notifier,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->get('per_page', 25), 100);

        $query = OnboardingRequest::query()
            ->with(['employee', 'author', 'desk.floor.building'])
            ->latest();

        if ($this->isItOnlyUser($request)) {
            $query->whereIn('status', OnboardingRequest::IT_QUEUE_STATUSES);
        }

        $paginator = $query->paginate($perPage);

        return response()->json($paginator);
    }

    public function show(Request $request, OnboardingRequest $onboarding): JsonResponse
    {
        if ($this->isItOnlyUser($request) && ! $onboarding->isVisibleToIt()) {
            return response()->json(['message' => 'This onboarding request is not available for IT.'], 403);
        }

        $onboarding->load(['employee.department', 'author', 'desk.floor.building']);

        return response()->json($onboarding);
    }

    public function store(StoreOnboardingRequestRequest $request): JsonResponse
    {
        $employee = Employee::query()->findOrFail($request->integer('employee_id'));

        $open = OnboardingRequest::query()
            ->where('employee_id', $employee->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->exists();

        if ($open) {
            return response()->json(['message' => 'This employee already has an open onboarding request.'], 422);
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
                return response()->json(['message' => 'Selected desk is already occupied by another employee.'], 422);
            }
            throw $e;
        }

        AuditLogger::log($request->user()->id, 'onboarding.created', $onboarding, null, $onboarding->toArray());
        event(new OnboardingStatusUpdated($onboarding->fresh(['employee', 'desk'])));

        return response()->json($onboarding->load(['employee', 'desk']), 201);
    }

    public function assignDesk(AssignDeskOnboardingRequest $request, OnboardingRequest $onboarding): JsonResponse
    {
        if (in_array($onboarding->status, ['completed', 'cancelled'], true)) {
            return response()->json(['message' => 'This onboarding request is closed.'], 422);
        }

        $employee = $onboarding->employee;
        $desk = Desk::query()->findOrFail($request->integer('desk_id'));

        $blocked = DeskAllocation::query()
            ->where('desk_id', $desk->id)
            ->whereNull('valid_to')
            ->where('employee_id', '!=', $employee->id)
            ->exists();
        if ($blocked) {
            return response()->json(['message' => 'Desk is already occupied by another employee.'], 422);
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
        $this->notifier->notifyUsersWithRoles(['it', 'admin'], new OnboardingStatusChanged($onboarding, 'Desk assigned for new hire'));
        event(new OnboardingStatusUpdated($onboarding->load(['employee', 'desk'])));

        return response()->json($onboarding);
    }

    public function forwardToIt(Request $request, OnboardingRequest $onboarding): JsonResponse
    {
        $request->validate(['it_notes' => ['nullable', 'string', 'max:5000']]);

        if (! $onboarding->desk_id) {
            return response()->json(['message' => 'Assign a desk before forwarding to IT.'], 422);
        }

        if (in_array($onboarding->status, ['completed', 'cancelled'], true)) {
            return response()->json(['message' => 'This onboarding request is closed.'], 422);
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

        return response()->json($onboarding);
    }

    public function itSetupStart(Request $request, OnboardingRequest $onboarding): JsonResponse
    {
        if ($response = $this->ensureItCanManage($request, $onboarding)) {
            return $response;
        }

        if ($onboarding->status !== 'it_pending') {
            return response()->json(['message' => 'IT can only start after the request is forwarded to IT.'], 422);
        }

        $before = $onboarding->toArray();
        $onboarding->update([
            'status' => 'it_in_progress',
            'it_setup_started_at' => now(),
        ]);

        AuditLogger::log($request->user()->id, 'onboarding.it_started', $onboarding, $before, $onboarding->toArray());
        $this->notifier->notifyUsersWithRoles(['hr', 'admin'], new OnboardingStatusChanged($onboarding, 'IT setup started'));
        event(new OnboardingStatusUpdated($onboarding->load(['employee', 'desk'])));

        return response()->json($onboarding);
    }

    public function itSetupComplete(Request $request, OnboardingRequest $onboarding): JsonResponse
    {
        if ($response = $this->ensureItCanManage($request, $onboarding)) {
            return $response;
        }

        $request->validate(['it_notes' => ['nullable', 'string', 'max:5000']]);

        if (! in_array($onboarding->status, ['it_pending', 'it_in_progress'], true)) {
            return response()->json(['message' => 'IT cannot complete setup for this status.'], 422);
        }

        $before = $onboarding->toArray();
        $onboarding->update([
            'status' => 'it_complete',
            'it_setup_completed_at' => now(),
            'it_notes' => $request->input('it_notes', $onboarding->it_notes),
        ]);

        AuditLogger::log($request->user()->id, 'onboarding.it_completed', $onboarding, $before, $onboarding->toArray());
        $this->notifier->notifyUsersWithRoles(['hr', 'admin'], new OnboardingStatusChanged($onboarding, 'IT setup completed'));
        event(new OnboardingStatusUpdated($onboarding->load(['employee', 'desk'])));

        return response()->json($onboarding);
    }

    public function complete(Request $request, OnboardingRequest $onboarding): JsonResponse
    {
        if ($onboarding->status !== 'it_complete') {
            return response()->json(['message' => 'HR can complete onboarding only after IT marks system setup complete.'], 422);
        }

        if (! $onboarding->desk_id) {
            return response()->json(['message' => 'Assign a desk before completing onboarding.'], 422);
        }

        $before = $onboarding->toArray();
        $onboarding->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        AuditLogger::log($request->user()->id, 'onboarding.completed', $onboarding, $before, $onboarding->toArray());
        event(new OnboardingStatusUpdated($onboarding->load(['employee', 'desk'])));

        return response()->json($onboarding);
    }

    public function cancel(Request $request, OnboardingRequest $onboarding): JsonResponse
    {
        if (in_array($onboarding->status, ['completed', 'cancelled'], true)) {
            return response()->json(['message' => 'Request is already closed.'], 422);
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

        return response()->json($onboarding);
    }

    private function isItOnlyUser(Request $request): bool
    {
        $user = $request->user();

        return $user?->hasRole('it') && ! $user->hasAnyRole(['admin', 'hr']);
    }

    private function ensureItCanManage(Request $request, OnboardingRequest $onboarding): ?JsonResponse
    {
        if (! $request->user()?->hasRole('it')) {
            return response()->json(['message' => 'Only IT can perform system setup for onboarding.'], 403);
        }

        if (! $onboarding->isVisibleToIt()) {
            return response()->json(['message' => 'This onboarding request is not available for IT.'], 403);
        }

        return null;
    }
}
