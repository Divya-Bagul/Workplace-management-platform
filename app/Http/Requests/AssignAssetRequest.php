<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'it']) ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
