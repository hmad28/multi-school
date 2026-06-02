<?php

namespace App\Actions\Attendance;

use App\Models\TeacherAttendance;
use App\Models\User;

class CorrectTeacherAttendanceAction
{
    public function execute(User $user, TeacherAttendance $attendance, string $statusId, ?string $note = null): void
    {
        $attendance->update([
            'attendance_status_id' => $statusId,
            'note' => $note,
            'corrected_by' => $user->id,
            'corrected_at' => now(),
        ]);
    }
}
