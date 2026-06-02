<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAttendance extends Model
{
    use BelongsToSchool;
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'school_id',
        'teacher_id',
        'attendance_status_id',
        'date',
        'note',
        'submitted_by',
        'corrected_by',
        'corrected_at',
        'locked_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'corrected_at' => 'datetime',
            'locked_at' => 'datetime',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(AttendanceStatus::class, 'attendance_status_id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function corrector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'corrected_by');
    }
}
