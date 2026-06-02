<?php

namespace App\Http\Requests\MasterData;

use App\Support\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('academic.manage');
    }

    public function rules(): array
    {
        $year = $this->route('year');

        return [
            'name' => ['required', 'string', 'max:30', Rule::unique('academic_years', 'name')->where('school_id', TenantContext::id())->ignore($year)],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'is_active' => ['boolean'],
        ];
    }
}
