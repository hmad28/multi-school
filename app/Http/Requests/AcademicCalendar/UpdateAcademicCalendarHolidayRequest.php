<?php

namespace App\Http\Requests\AcademicCalendar;

use App\Support\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAcademicCalendarHolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('academic-calendar.manage');
    }

    public function rules(): array
    {
        return [
            'date' => [
                'required',
                'date',
                Rule::unique('academic_calendar_holidays', 'date')
                    ->ignore($this->route('holiday'))
                    ->where('school_id', TenantContext::id())
                    ->whereNull('deleted_at'),
            ],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
