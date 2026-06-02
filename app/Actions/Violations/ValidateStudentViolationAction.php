<?php

namespace App\Actions\Violations;

use App\Models\StudentViolation;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ValidateStudentViolationAction
{
    public function execute(User $user, StudentViolation $violation): void
    {
        if ($violation->status !== 'pending') {
            throw ValidationException::withMessages(['status' => 'Pelanggaran sudah diproses.']);
        }

        $violation->update([
            'status' => 'validated',
            'validated_by' => $user->id,
            'validated_at' => now(),
            'rejection_reason' => null,
        ]);
    }
}
