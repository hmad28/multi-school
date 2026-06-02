<?php

namespace App\Http\Requests\Violations;

use Illuminate\Foundation\Http\FormRequest;

class RejectStudentViolationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('violations.validate');
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'max:255'],
        ];
    }
}
