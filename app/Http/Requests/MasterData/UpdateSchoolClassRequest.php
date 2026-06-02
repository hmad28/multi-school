<?php

namespace App\Http\Requests\MasterData;

use App\Support\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSchoolClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('classes.manage');
    }

    public function rules(): array
    {
        $class = $this->route('class');

        return [
            'academic_level_id' => ['required', 'uuid', Rule::exists('academic_levels', 'id')->where('school_id', TenantContext::id())],
            'name' => ['required', 'string', 'max:50', Rule::unique('classes', 'name')->where('school_id', TenantContext::id())->where('academic_level_id', $this->input('academic_level_id'))->ignore($class)],
            'homeroom_teacher_id' => ['nullable', 'uuid', Rule::exists('teachers', 'id')->where('school_id', TenantContext::id())->where('status', 'active')],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ];
    }
}
