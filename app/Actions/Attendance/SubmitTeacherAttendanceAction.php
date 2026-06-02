<?php

namespace App\Actions\Attendance;

use App\Models\AttendanceStatus;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Models\TeacherAttendanceSubmission;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubmitTeacherAttendanceAction
{
    public function execute(User $user, string $date, array $rows): void
    {
        DB::transaction(function () use ($user, $date, $rows): void {
            if (TeacherAttendanceSubmission::query()
                ->whereDate('date', $date)
                ->exists()) {
                throw ValidationException::withMessages([
                    'date' => 'Absensi guru untuk tanggal ini sudah dikirim.',
                ]);
            }

            $teachers = Teacher::query()
                ->where('status', 'active')
                ->pluck('id')
                ->all();

            $statuses = AttendanceStatus::query()
                ->where('is_active', true)
                ->pluck('id', 'id')
                ->all();

            $rowsByTeacher = collect($rows)->keyBy('teacher_id');
            $now = now();

            foreach ($teachers as $teacherId) {
                $row = $rowsByTeacher->get($teacherId, []);
                $statusId = $row['attendance_status_id'] ?? null;

                if (! $statusId || ! isset($statuses[$statusId])) {
                    throw ValidationException::withMessages([
                        'attendances' => 'Status absensi tidak valid.',
                    ]);
                }

                TeacherAttendance::create([
                    'teacher_id' => $teacherId,
                    'attendance_status_id' => $statusId,
                    'date' => $date,
                    'note' => $row['note'] ?? null,
                    'submitted_by' => $user->id,
                    'locked_at' => $now,
                ]);
            }

            TeacherAttendanceSubmission::create([
                'date' => $date,
                'submitted_by' => $user->id,
                'submitted_at' => $now,
            ]);
        });
    }
}
