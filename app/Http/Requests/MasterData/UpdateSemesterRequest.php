<?php

namespace App\Http\Requests\MasterData;

use App\Support\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSemesterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('academic.manage');
    }

    public function rules(): array
    {
        $semester = $this->route('semester');

        return [
            'academic_year_id' => ['required', 'uuid', Rule::exists('academic_years', 'id')->where('school_id', TenantContext::id())],
            'name' => ['required', 'string', 'max:30', Rule::unique('semesters', 'name')->where('school_id', TenantContext::id())->where('academic_year_id', $this->input('academic_year_id'))->ignore($semester)],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'is_active' => ['boolean'],
        ];
    }
}
