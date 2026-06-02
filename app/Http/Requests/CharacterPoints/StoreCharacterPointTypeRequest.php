<?php

namespace App\Http\Requests\CharacterPoints;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCharacterPointTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('character-points.manage-types');
    }

    public function rules(): array
    {
        return [
            'category' => ['required', 'string', 'max:30'],
            'name' => ['required', 'string', 'max:150'],
            'points' => ['required', 'integer', 'min:1', 'max:100'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ];
    }
}
