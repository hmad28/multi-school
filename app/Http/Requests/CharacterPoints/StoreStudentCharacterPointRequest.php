<?php

namespace App\Http\Requests\CharacterPoints;

use App\Support\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentCharacterPointRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('character-points.input');
    }

    public function rules(): array
    {
        $schoolId = TenantContext::id();

        return [
            'student_id' => ['required', 'uuid', Rule::exists('students', 'id')->where('school_id', $schoolId)->where('status', 'active')],
            'character_point_type_id' => ['required', 'uuid', Rule::exists('character_point_types', 'id')->where('status', 'active')],
            'date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
