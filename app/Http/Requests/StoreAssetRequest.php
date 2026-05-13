<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'it']) ?? false;
    }

    /**
     * @return array<string, array<int, string|Rule>>
     */
    public function rules(): array
    {
        return [
            'asset_type_id' => ['required', 'exists:asset_types,id'],
            'asset_tag' => ['required', 'string', 'max:64', 'unique:assets,asset_tag'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['in_stock'])],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
