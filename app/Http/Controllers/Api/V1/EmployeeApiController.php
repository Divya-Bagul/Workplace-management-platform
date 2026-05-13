<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->get('per_page', 25), 100);

        $paginator = Employee::query()
            ->with(['department', 'building', 'floor'])
            ->orderBy('name')
            ->paginate($perPage);

        return response()->json($paginator);
    }

    public function show(Employee $employee): JsonResponse
    {
        $employee->load(['department', 'building', 'floor', 'reportingManager']);

        return response()->json($employee);
    }

    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $employee = Employee::query()->create($request->validated());

        AuditLogger::log($request->user()->id, 'employee.created', $employee, null, $employee->toArray());

        return response()->json($employee->load(['department', 'building', 'floor']), 201);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        $before = $employee->toArray();
        $employee->update($request->validated());

        AuditLogger::log($request->user()->id, 'employee.updated', $employee, $before, $employee->toArray());

        return response()->json($employee->load(['department', 'building', 'floor']));
    }
}
