<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('attendance.teachers.input');
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'attendances' => ['required', 'array'],
            'attendances.*.teacher_id' => ['required', 'uuid', Rule::exists('teachers', 'id')->where('status', 'active')],
            'attendances.*.attendance_status_id' => ['required', 'uuid', Rule::exists('attendance_statuses', 'id')->where('is_active', true)],
            'attendances.*.note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
