<?php

namespace App\Http\Requests\Registration;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterSchoolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'school_name' => ['required', 'string', 'max:150'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'password' => ['required', 'confirmed', Password::defaults()],
            'plan' => ['nullable', 'string', Rule::in(['trial', 'standar', 'plus', 'custom'])],
            'period' => ['nullable', 'string', Rule::in(['monthly', 'yearly'])],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function registrationData(): array
    {
        $validated = $this->validated();

        // Marketing "trial"/"custom" map onto the standar plan for the seeded subscription.
        $plan = $validated['plan'] ?? 'standar';
        if (in_array($plan, ['trial', 'custom'], true)) {
            $plan = 'standar';
        }

        return [
            'school_name' => $validated['school_name'],
            'admin_name' => $validated['admin_name'],
            'admin_email' => $validated['admin_email'],
            'password' => $validated['password'],
            'plan' => $plan,
            'period' => $validated['period'] ?? 'monthly',
        ];
    }
}
