<?php

namespace App\Services;

use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentCharacterPoint;
use App\Models\StudentViolation;

class StudentReportData
{
    public function __construct(
        private readonly ViolationPointService $pointService,
        private readonly CharacterPointService $characterPointService,
    ) {}

    public function build(Student $student, ?string $from = null, ?string $to = null): array
    {
        $from ??= today()->startOfMonth()->toDateString();
        $to ??= today()->toDateString();
        $semester = Semester::query()->where('is_active', true)->first();

        $student->load('schoolClass.academicLevel');

        $attendances = StudentAttendance::query()
            ->with('status:id,code,name')
            ->where('student_id', $student->id)
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->orderByDesc('date')
            ->get();

        $violations = StudentViolation::query()
            ->with('violationType:id,name')
            ->where('student_id', $student->id)
            ->where('status', 'validated')
            ->when($semester, fn ($query) => $query->where('semester_id', $semester->id))
            ->latest('date')
            ->get();

        $characterPoints = StudentCharacterPoint::query()
            ->with('characterPointType:id,name')
            ->where('student_id', $student->id)
            ->when($semester, fn ($query) => $query->where('semester_id', $semester->id))
            ->latest('date')
            ->get();

        $totalPoints = $semester ? $this->pointService->totalFor($student, $semester->id) : 0;
        $totalCharacterPoints = $semester ? $this->characterPointService->totalFor($student, $semester->id) : 0;
        $threshold = $this->pointService->thresholdForPoints($totalPoints);

        return [
            'student' => [
                'id' => $student->id,
                'nis' => $student->nis,
                'nisn' => $student->nisn,
                'full_name' => $student->name,
                'gender' => $student->gender,
                'class_name' => $student->schoolClass?->display_name,
                'guardian_name' => $student->guardian_name,
                'guardian_phone' => $student->guardian_phone,
                'address' => $student->address,
            ],
            'filters' => [
                'from' => $from,
                'to' => $to,
            ],
            'summary' => [
                'attendance_total' => $attendances->count(),
                'present_count' => $attendances->filter(fn (StudentAttendance $attendance) => $attendance->status?->code === 'H')->count(),
                'late_count' => $attendances->filter(fn (StudentAttendance $attendance) => $attendance->status?->code === 'T')->count(),
                'absent_count' => $attendances->filter(fn (StudentAttendance $attendance) => $attendance->status?->code === 'A')->count(),
                'violation_count' => $violations->count(),
                'character_count' => $characterPoints->count(),
            ],
            'attendanceSummary' => $attendances
                ->groupBy(fn (StudentAttendance $attendance) => $attendance->status?->code ?? '-')
                ->map(fn ($rows, string $code) => [
                    'code' => $code,
                    'name' => $rows->first()->status?->name ?? 'Tidak diketahui',
                    'total' => $rows->count(),
                ])
                ->values(),
            'attendances' => $attendances->map(fn (StudentAttendance $attendance) => [
                'id' => $attendance->id,
                'date' => $attendance->date?->toDateString(),
                'code' => $attendance->status?->code,
                'status' => $attendance->status?->name,
                'note' => $attendance->note,
            ]),
            'violations' => $violations->map(fn (StudentViolation $violation) => [
                'id' => $violation->id,
                'date' => $violation->date?->toDateString(),
                'violation_type' => $violation->violationType?->name,
                'category' => $violation->category_snapshot,
                'points' => $violation->points_snapshot,
                'note' => $violation->note,
            ]),
            'characterPoints' => $characterPoints->map(fn (StudentCharacterPoint $point) => [
                'id' => $point->id,
                'date' => $point->date?->toDateString(),
                'type' => $point->characterPointType?->name,
                'category' => $point->category_snapshot,
                'points' => $point->points_snapshot,
                'note' => $point->note,
            ]),
            'pointSummary' => [
                'total' => $totalPoints,
                'character_total' => $totalCharacterPoints,
                'threshold' => $threshold ? [
                    'points' => $threshold->points,
                    'label' => $threshold->label,
                ] : null,
            ],
        ];
    }
}
