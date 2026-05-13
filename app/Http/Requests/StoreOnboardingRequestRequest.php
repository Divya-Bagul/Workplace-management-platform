<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOnboardingRequestRequest extends FormRequest
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
            'employee_id' => ['required', 'exists:employees,id'],
            'desk_id' => ['nullable', 'exists:desks,id'],
            'hr_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
