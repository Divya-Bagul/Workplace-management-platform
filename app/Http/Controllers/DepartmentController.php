<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        $departments = Department::query()->orderBy('name')->get();

        return view('admin.departments.index', compact('departments'));
    }

    public function create(): View
    {
        return view('admin.departments.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
        ]);

        $department = Department::query()->create($data);

        AuditLogger::log($request->user()->id, 'department.created', $department, null, $department->only(['name', 'code']));

        return redirect()->route('admin.departments.index')->with('status', __('Department created.'));
    }

    public function edit(Department $department): View
    {
        return view('admin.departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
        ]);

        $before = $department->only(['name', 'code']);
        $department->update($data);

        AuditLogger::log($request->user()->id, 'department.updated', $department, $before, $department->only(['name', 'code']));

        return redirect()->route('admin.departments.index')->with('status', __('Department updated.'));
    }

    public function destroy(Request $request, Department $department): RedirectResponse
    {
        AuditLogger::log($request->user()->id, 'department.deleted', $department, $department->only(['name']), null);
        $department->delete();

        return redirect()->route('admin.departments.index')->with('status', __('Department removed.'));
    }
}
