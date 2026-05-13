<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'hr']) ?? false;
    }

    /**
     * @return array<string, array<int, string|Rule>>
     */
    public function rules(): array
    {
        return [
            'employee_code' => ['required', 'string', 'max:64', 'unique:employees,employee_code'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'designation' => ['nullable', 'string', 'max:255'],
            'joining_date' => ['nullable', 'date'],
            'reporting_manager_id' => ['nullable', 'exists:employees,id'],
            'building_id' => ['nullable', 'exists:buildings,id'],
            'floor_id' => ['nullable', 'exists:floors,id'],
            'employment_status' => ['nullable', Rule::in(['active', 'inactive', 'on_leave'])],
        ];
    }
}
