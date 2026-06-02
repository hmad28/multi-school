<?php

namespace App\Services;

use App\Models\AttendanceStatus;
use App\Models\QrAttendanceSession;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentViolation;
use App\Models\User;
use App\Models\ViolationType;
use App\Support\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class QrAttendanceService
{
    public function studentToken(Student $student): string
    {
        if (filled($student->qr_token)) {
            return $student->qr_token;
        }

        return $this->issueStudentToken($student);
    }

    public function issueStudentToken(Student $student): string
    {
        $token = 'student:'.Str::random(48);
        $student->forceFill([
            'qr_token' => $token,
            'qr_token_hash' => hash('sha256', $token),
        ])->save();

        return $token;
    }

    /** @return array{session: QrAttendanceSession, token: string} */
    public function createSession(User $user, SchoolClass $schoolClass, string $date, string $scanType): array
    {
        $this->ensureScanType($scanType);

        $token = 'session:'.Str::random(48);
        $session = QrAttendanceSession::create([
            'class_id' => $schoolClass->id,
            'date' => $date,
            'scan_type' => $scanType,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addMinutes(10),
            'created_by' => $user->id,
        ]);

        return ['session' => $session, 'token' => $token];
    }

    public function scan(User $user, string $sessionToken, string $studentToken): StudentAttendance
    {
        $session = QrAttendanceSession::query()
            ->where('token_hash', hash('sha256', $sessionToken))
            ->first();

        if (! $session || $session->expires_at->isPast()) {
            throw ValidationException::withMessages(['session_token' => 'Sesi QR sudah kedaluwarsa atau tidak valid.']);
        }

        $student = Student::query()
            ->where('qr_token_hash', hash('sha256', $studentToken))
            ->where('class_id', $session->class_id)
            ->where('status', 'active')
            ->first();

        if (! $student) {
            throw ValidationException::withMessages(['student_token' => 'QR siswa tidak valid untuk kelas ini.']);
        }

        return $this->recordScan($user, $student, $session->date->toDateString(), $session->scan_type);
    }

    public function scanStudent(User $user, string $studentToken, string $date, string $scanType, bool $forceUpdate = false): StudentAttendance
    {
        $this->ensureScanType($scanType);

        $student = Student::query()
            ->where('qr_token_hash', hash('sha256', $studentToken))
            ->where('status', 'active')
            ->first();

        if (! $student) {
            throw ValidationException::withMessages(['student_token' => 'QR siswa tidak valid atau siswa tidak aktif.']);
        }

        return $this->recordScan($user, $student, $date, $scanType, $forceUpdate);
    }

    private function recordScan(User $user, Student $student, string $date, string $scanType, bool $forceUpdate = false): StudentAttendance
    {
        return DB::transaction(function () use ($user, $student, $date, $scanType, $forceUpdate): StudentAttendance {
            $holiday = app(AttendanceCalendarService::class)->holidayFor($date);

            if ($holiday) {
                throw ValidationException::withMessages(['date' => 'Tanggal ini adalah hari libur: '.$holiday->name.'. Absensi tidak dicatat.']);
            }

            $present = AttendanceStatus::query()->where('code', 'H')->firstOrFail();
            $late = AttendanceStatus::query()->where('code', 'T')->first();
            $now = now();

            $attendance = StudentAttendance::query()
                ->where('student_id', $student->id)
                ->whereDate('date', $date)
                ->first();

            if (! $attendance) {
                $attendance = StudentAttendance::query()->create([
                    'student_id' => $student->id,
                    'date' => $date,
                    'class_id' => $student->class_id,
                    'attendance_status_id' => $present->id,
                    'submitted_by' => $user->id,
                    'locked_at' => $now,
                ]);
            }

            if ($scanType === 'arrival') {
                if ($attendance->arrival_time && ! $forceUpdate) {
                    throw ValidationException::withMessages([
                        'duplicate_scan' => $student->name.' sudah scan datang pukul '.$attendance->arrival_time->format('H:i').'.',
                    ]);
                }

                $attendance->arrival_time = $now;
                $attendance->arrival_source = 'qr';
                $this->applyLateStatus($attendance, $late, $now, $user, $student, $date);
            } else {
                if ($attendance->departure_time && ! $forceUpdate) {
                    throw ValidationException::withMessages([
                        'duplicate_scan' => $student->name.' sudah scan pulang pukul '.$attendance->departure_time->format('H:i').'.',
                    ]);
                }

                $attendance->departure_time = $now;
                $attendance->departure_source = 'qr';
            }

            $attendance->save();

            return $attendance->refresh()->load(['student.schoolClass.academicLevel', 'status']);
        });
    }

    private function applyLateStatus(StudentAttendance $attendance, ?AttendanceStatus $late, $scanTime, User $user, Student $student, string $date): void
    {
        $school = TenantContext::school();
        $lateAfter = $school?->student_attendance_late_after;

        if (! $late || blank($lateAfter)) {
            return;
        }

        $cutoff = $scanTime->copy()->setTimeFromTimeString((string) $lateAfter);

        if ($scanTime->lte($cutoff)) {
            return;
        }

        $attendance->attendance_status_id = $late->id;
        $lateNote = 'Terlambat otomatis dari scan QR pukul '.$scanTime->format('H:i').'.';
        $attendance->note = filled($attendance->note) ? $attendance->note.' '.$lateNote : $lateNote;
        $this->recordLateViolation($user, $student, $date, $lateNote);
    }

    private function recordLateViolation(User $user, Student $student, string $date, string $note): void
    {
        $semester = Semester::query()->where('is_active', true)->first();
        $type = ViolationType::query()->where('name', 'Terlambat masuk kelas')->where('status', 'active')->first();

        if (! $semester || ! $type) {
            return;
        }

        StudentViolation::query()->firstOrCreate(
            [
                'student_id' => $student->id,
                'violation_type_id' => $type->id,
                'semester_id' => $semester->id,
                'date' => $date,
            ],
            [
                'class_id' => $student->class_id,
                'category_snapshot' => $type->category,
                'points_snapshot' => $type->points,
                'note' => $note,
                'status' => 'validated',
                'reported_by' => $user->id,
                'validated_by' => $user->id,
                'validated_at' => now(),
            ]
        );
    }

    private function ensureScanType(string $scanType): void
    {
        if (! in_array($scanType, ['arrival', 'departure'], true)) {
            throw ValidationException::withMessages(['scan_type' => 'Tipe scan tidak valid.']);
        }
    }
}
