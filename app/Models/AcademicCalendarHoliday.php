<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicCalendarHoliday extends Model
{
    use BelongsToSchool;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'date',
        'name',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }
}
