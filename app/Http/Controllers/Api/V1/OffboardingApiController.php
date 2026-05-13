<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\OffboardingStatusUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOffboardingRequestRequest;
use App\Models\Employee;
use App\Models\OffboardingRequest;
use App\Notifications\OffboardingInitiatedForIt;
use App\Services\AuditLogger;
use App\Services\DeskAssignmentService;
use App\Services\WorkplaceNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OffboardingApiController extends Controller
{
    public function __construct(
        private readonly DeskAssignmentService $desks,
        private readonly WorkplaceNotifier $notifier,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->get('per_page', 25), 100);

        $paginator = OffboardingRequest::query()
            ->with(['employee', 'initiator'])
            ->latest()
            ->paginate($perPage);

        return response()->json($paginator);
    }

    public function show(OffboardingRequest $offboarding): JsonResponse
    {
        $offboarding->load(['employee.department', 'initiator']);

        return response()->json($offboarding);
    }

    public function store(StoreOffboardingRequestRequest $request): JsonResponse
    {
        $employee = Employee::query()->findOrFail($request->integer('employee_id'));

        $open = OffboardingRequest::query()
            ->where('employee_id', $employee->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->exists();

        if ($open) {
            return response()->json(['message' => 'This employee already has an open offboarding request.'], 422);
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

        return response()->json($offboarding, 201);
    }

    public function startRecovery(Request $request, OffboardingRequest $offboarding): JsonResponse
    {
        if ($offboarding->status !== 'pending') {
            return response()->json(['message' => 'Recovery can only start from pending.'], 422);
        }

        $before = $offboarding->toArray();
        $offboarding->update([
            'status' => 'recovery_in_progress',
            'assets_recovery_started_at' => now(),
        ]);

        AuditLogger::log($request->user()->id, 'offboarding.recovery_started', $offboarding, $before, $offboarding->toArray());
        event(new OffboardingStatusUpdated($offboarding->load('employee')));

        return response()->json($offboarding);
    }

    public function markAssetsRecovered(Request $request, OffboardingRequest $offboarding): JsonResponse
    {
        if (! in_array($offboarding->status, ['pending', 'recovery_in_progress'], true)) {
            return response()->json(['message' => 'Invalid status for this step.'], 422);
        }

        $before = $offboarding->toArray();
        $offboarding->update([
            'status' => 'assets_recovered',
            'assets_recovered_at' => now(),
        ]);

        AuditLogger::log($request->user()->id, 'offboarding.assets_recovered', $offboarding, $before, $offboarding->toArray());
        event(new OffboardingStatusUpdated($offboarding->load('employee')));

        return response()->json($offboarding);
    }

    public function releaseDesk(Request $request, OffboardingRequest $offboarding): JsonResponse
    {
        if ($offboarding->status !== 'assets_recovered') {
            return response()->json(['message' => 'Mark assets as recovered before releasing the desk.'], 422);
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

        return response()->json($offboarding);
    }

    public function complete(Request $request, OffboardingRequest $offboarding): JsonResponse
    {
        if ($offboarding->status !== 'desk_released') {
            return response()->json(['message' => 'Release the desk before completing offboarding.'], 422);
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

        return response()->json($offboarding);
    }

    public function cancel(Request $request, OffboardingRequest $offboarding): JsonResponse
    {
        if (in_array($offboarding->status, ['completed', 'cancelled'], true)) {
            return response()->json(['message' => 'Request is already closed.'], 422);
        }

        $before = $offboarding->toArray();
        $offboarding->update(['status' => 'cancelled']);

        AuditLogger::log($request->user()->id, 'offboarding.cancelled', $offboarding, $before, $offboarding->toArray());
        event(new OffboardingStatusUpdated($offboarding->load('employee')));

        return response()->json($offboarding);
    }
}
