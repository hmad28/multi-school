<?php

namespace App\Actions\CharacterPoints;

use App\Models\CharacterPointType;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentCharacterPoint;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateStudentCharacterPointAction
{
    public function execute(User $user, Student $student, CharacterPointType $type, string $date, ?string $note = null): StudentCharacterPoint
    {
        return DB::transaction(function () use ($user, $student, $type, $date, $note): StudentCharacterPoint {
            $semester = Semester::query()->where('is_active', true)->firstOrFail();

            return StudentCharacterPoint::create([
                'student_id' => $student->id,
                'class_id' => $student->class_id,
                'character_point_type_id' => $type->id,
                'semester_id' => $semester->id,
                'date' => $date,
                'category_snapshot' => $type->category,
                'points_snapshot' => $type->points,
                'note' => $note,
                'recorded_by' => $user->id,
            ]);
        });
    }
}
