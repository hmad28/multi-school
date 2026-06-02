<?php

namespace App\Actions\Attendance;

use App\Models\AttendanceClassSubmission;
use App\Models\AttendanceStatus;
use App\Models\SchoolClass;
use App\Models\StudentAttendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubmitStudentAttendanceAction
{
    public function execute(User $user, SchoolClass $schoolClass, string $date, array $rows): Collection
    {
        return DB::transaction(function () use ($user, $schoolClass, $date, $rows): Collection {
            if (AttendanceClassSubmission::where('class_id', $schoolClass->id)->where('date', $date)->exists()) {
                throw ValidationException::withMessages([
                    'date' => 'Absensi kelas untuk tanggal ini sudah dikirim.',
                ]);
            }

            $students = $schoolClass->students()
                ->where('status', 'active')
                ->pluck('id')
                ->all();

            $statuses = AttendanceStatus::query()
                ->where('is_active', true)
                ->pluck('id', 'id')
                ->all();

            $rowsByStudent = collect($rows)->keyBy('student_id');
            $now = now();

            $created = new Collection;

            foreach ($students as $studentId) {
                $row = $rowsByStudent->get($studentId, []);
                $statusId = $row['attendance_status_id'] ?? null;

                if (! $statusId || ! isset($statuses[$statusId])) {
                    throw ValidationException::withMessages([
                        'attendances' => 'Status absensi tidak valid.',
                    ]);
                }

                $created->push(StudentAttendance::create([
                    'student_id' => $studentId,
                    'class_id' => $schoolClass->id,
                    'attendance_status_id' => $statusId,
                    'date' => $date,
                    'note' => $row['note'] ?? null,
                    'submitted_by' => $user->id,
                    'locked_at' => $now,
                ]));
            }

            AttendanceClassSubmission::create([
                'class_id' => $schoolClass->id,
                'date' => $date,
                'submitted_by' => $user->id,
                'submitted_at' => $now,
            ]);

            return $created;
        });
    }
}
