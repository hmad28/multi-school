<?php

namespace App\Http\Requests\MasterData;

use App\Support\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAcademicLevelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('academic.manage');
    }

    public function rules(): array
    {
        $level = $this->route('level');

        return [
            'name' => ['required', 'string', 'max:50', Rule::unique('academic_levels', 'name')->where('school_id', TenantContext::id())->ignore($level)],
            'numeric_value' => ['required', 'integer', 'min:1', 'max:12', Rule::unique('academic_levels', 'numeric_value')->where('school_id', TenantContext::id())->ignore($level)],
        ];
    }
}
