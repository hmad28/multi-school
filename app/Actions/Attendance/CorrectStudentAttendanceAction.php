<?php

namespace App\Actions\Attendance;

use App\Models\StudentAttendance;
use App\Models\User;

class CorrectStudentAttendanceAction
{
    public function execute(User $user, StudentAttendance $attendance, string $statusId, ?string $note = null): void
    {
        $attendance->update([
            'attendance_status_id' => $statusId,
            'note' => $note,
            'corrected_by' => $user->id,
            'corrected_at' => now(),
        ]);
    }
}
