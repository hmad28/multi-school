<?php

namespace App\Http\Requests\MasterData;

use App\Support\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('students.update');
    }

    public function rules(): array
    {
        $student = $this->route('student');

        return [
            'name' => ['required', 'string', 'max:150'],
            'nis' => ['required', 'string', 'max:50', Rule::unique('students', 'nis')->where('school_id', TenantContext::id())->ignore($student)],
            'nisn' => ['nullable', 'string', 'max:20', Rule::unique('students', 'nisn')->where('school_id', TenantContext::id())->ignore($student)],
            'class_id' => ['nullable', 'uuid', Rule::exists('classes', 'id')->where('school_id', TenantContext::id())->where('status', 'active')],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'guardian_name' => ['nullable', 'string', 'max:100'],
            'guardian_phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
