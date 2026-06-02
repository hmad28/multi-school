<?php

namespace App\Http\Requests\MasterData;

use App\Support\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('teachers.update');
    }

    public function rules(): array
    {
        $teacher = $this->route('teacher');

        return [
            'nip' => ['nullable', 'string', 'max:30', Rule::unique('teachers', 'nip')->where('school_id', TenantContext::id())->ignore($teacher)],
            'full_name' => ['required', 'string', 'max:100'],
            'position' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'can_input_teacher_attendance' => ['boolean'],
        ];
    }
}
