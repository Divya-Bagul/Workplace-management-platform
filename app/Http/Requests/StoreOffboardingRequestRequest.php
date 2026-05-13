<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOffboardingRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'hr']) ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'last_working_day' => ['required', 'date'],
            'hr_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
