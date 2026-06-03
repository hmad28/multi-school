<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentCharacterPoint;

class CharacterPointService
{
    public function totalFor(Student $student, string $semesterId): int
    {
        return (int) StudentCharacterPoint::query()
            ->where('student_id', $student->id)
            ->where('semester_id', $semesterId)
            ->sum('points_snapshot');
    }
}
