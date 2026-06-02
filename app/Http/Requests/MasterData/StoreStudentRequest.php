<?php

namespace App\Http\Requests\MasterData;

use App\Support\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('students.create');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'nis' => ['required', 'string', 'max:50', Rule::unique('students', 'nis')->where('school_id', TenantContext::id())],
            'nisn' => ['nullable', 'string', 'max:20', Rule::unique('students', 'nisn')->where('school_id', TenantContext::id())],
            'class_id' => ['nullable', 'uuid', Rule::exists('classes', 'id')->where('school_id', TenantContext::id())->where('status', 'active')],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'guardian_name' => ['nullable', 'string', 'max:100'],
            'guardian_phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
