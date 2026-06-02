<?php

namespace App\Http\Requests\Attendance;

use App\Support\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('attendance.students.input');
    }

    public function rules(): array
    {
        $schoolId = TenantContext::id();

        return [
            'class_id' => ['required', 'uuid', Rule::exists('classes', 'id')->where('school_id', $schoolId)->where('status', 'active')],
            'date' => ['required', 'date'],
            'attendances' => ['required', 'array', 'min:1'],
            'attendances.*.student_id' => ['required', 'uuid', Rule::exists('students', 'id')->where('school_id', $schoolId)->where('status', 'active')],
            'attendances.*.attendance_status_id' => ['required', 'uuid', Rule::exists('attendance_statuses', 'id')->where('is_active', true)],
            'attendances.*.note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
