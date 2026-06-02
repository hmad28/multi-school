<?php

namespace App\Http\Requests\Violations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreViolationTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('violations.manage-types');
    }

    public function rules(): array
    {
        return [
            'category' => ['required', Rule::in(['ringan', 'sedang', 'berat'])],
            'name' => ['required', 'string', 'max:150'],
            'points' => ['required', 'integer', 'min:1', 'max:100'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
        ];
    }
}
