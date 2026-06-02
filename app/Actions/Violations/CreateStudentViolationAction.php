<?php

namespace App\Actions\Violations;

use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentViolation;
use App\Models\User;
use App\Models\ViolationType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CreateStudentViolationAction
{
    public function execute(User $user, Student $student, ViolationType $type, string $date, ?string $note = null, ?UploadedFile $evidence = null): StudentViolation
    {
        return DB::transaction(function () use ($user, $student, $type, $date, $note, $evidence): StudentViolation {
            $semester = Semester::query()->where('is_active', true)->firstOrFail();
            $evidencePath = $evidence?->store('violation-evidence', 'public');

            return StudentViolation::create([
                'student_id' => $student->id,
                'class_id' => $student->class_id,
                'violation_type_id' => $type->id,
                'semester_id' => $semester->id,
                'date' => $date,
                'category_snapshot' => $type->category,
                'points_snapshot' => $type->points,
                'note' => $note,
                'evidence_path' => $evidencePath,
                'status' => 'pending',
                'reported_by' => $user->id,
            ]);
        });
    }
}
