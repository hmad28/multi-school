<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolClass extends Model
{
    use BelongsToSchool;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'classes';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'school_id',
        'academic_level_id',
        'homeroom_teacher_id',
        'name',
        'status',
        'sort_order',
    ];

    protected $appends = ['display_name'];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function academicLevel(): BelongsTo
    {
        return $this->belongsTo(AcademicLevel::class);
    }

    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'homeroom_teacher_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    protected function displayName(): Attribute
    {
        return Attribute::get(function (): string {
            $level = $this->academicLevel;
            $section = trim((string) $this->name);

            if (! $level) {
                return $section;
            }

            if (preg_match('/^[A-Za-z]$/', $section) === 1) {
                return "Kelas {$level->numeric_value}{$section}";
            }

            return trim("Kelas {$level->numeric_value} {$section}");
        });
    }
}
