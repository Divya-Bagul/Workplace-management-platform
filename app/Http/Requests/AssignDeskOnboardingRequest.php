<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignDeskOnboardingRequest extends FormRequest
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
            'desk_id' => ['required', 'exists:desks,id'],
        ];
    }
}
