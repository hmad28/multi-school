<?php

namespace App\Http\Requests\Violations;

use App\Support\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentViolationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('violations.input');
    }

    public function rules(): array
    {
        $schoolId = TenantContext::id();

        return [
            'student_id' => ['required', 'uuid', Rule::exists('students', 'id')->where('school_id', $schoolId)->where('status', 'active')],
            'violation_type_id' => ['required', 'uuid', Rule::exists('violation_types', 'id')->where('status', 'active')],
            'date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:2000'],
            'evidence' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:4096'],
        ];
    }
}
