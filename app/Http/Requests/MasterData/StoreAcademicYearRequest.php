<?php

namespace App\Http\Requests\MasterData;

use App\Support\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('academic.manage');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:30', Rule::unique('academic_years', 'name')->where('school_id', TenantContext::id())],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'is_active' => ['boolean'],
        ];
    }
}
