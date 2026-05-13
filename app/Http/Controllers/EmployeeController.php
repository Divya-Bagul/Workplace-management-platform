<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Building;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Floor;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(): View
    {
        $employees = Employee::query()
            ->with(['department', 'building', 'floor'])
            ->orderBy('name')
            ->get();

        return view('hr.employees.index', compact('employees'));
    }

    public function create(): View
    {
        return view('hr.employees.create', $this->formOptions());
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $employee = Employee::query()->create($request->validated());

        AuditLogger::log($request->user()->id, 'employee.created', $employee, null, $employee->toArray());

        return redirect()->route('employees.index')->with('status', __('Employee saved.'));
    }

    public function edit(Employee $employee): View
    {
        return view('hr.employees.edit', array_merge(
            ['employee' => $employee],
            $this->formOptions()
        ));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $before = $employee->toArray();
        $employee->update($request->validated());

        AuditLogger::log($request->user()->id, 'employee.updated', $employee, $before, $employee->toArray());

        return redirect()->route('employees.index')->with('status', __('Employee updated.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'departments' => Department::query()->orderBy('name')->get(),
            'buildings' => Building::query()->with('floors')->orderBy('name')->get(),
            'floors' => Floor::query()->orderBy('building_id')->orderBy('level')->get(),
            'managers' => Employee::query()->where('employment_status', 'active')->orderBy('name')->get(),
        ];
    }
}
