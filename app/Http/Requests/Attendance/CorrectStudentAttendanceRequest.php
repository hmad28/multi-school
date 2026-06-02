<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CorrectStudentAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('attendance.students.correct');
    }

    public function rules(): array
    {
        return [
            'attendance_status_id' => ['required', 'uuid', Rule::exists('attendance_statuses', 'id')->where('is_active', true)],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
