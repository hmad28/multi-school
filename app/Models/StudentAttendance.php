<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAttendance extends Model
{
    use BelongsToSchool, HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'school_id',
        'student_id',
        'class_id',
        'attendance_status_id',
        'date',
        'note',
        'arrival_time',
        'departure_time',
        'arrival_source',
        'departure_source',
        'submitted_by',
        'corrected_by',
        'corrected_at',
        'locked_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'arrival_time' => 'datetime',
            'departure_time' => 'datetime',
            'corrected_at' => 'datetime',
            'locked_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
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
