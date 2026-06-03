<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentViolation;
use App\Models\ViolationThreshold;

class ViolationPointService
{
    public function totalFor(Student $student, string $semesterId): int
    {
        return (int) StudentViolation::query()
            ->where('student_id', $student->id)
            ->where('semester_id', $semesterId)
            ->where('status', 'validated')
            ->sum('points_snapshot');
    }

    public function thresholdForPoints(int $points): ?ViolationThreshold
    {
        return ViolationThreshold::query()
            ->where('points', '<=', $points)
            ->orderByDesc('points')
            ->first();
    }
}
