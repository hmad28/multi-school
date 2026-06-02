<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ViolationType extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'category',
        'name',
        'points',
        'status',
        'sort_order',
    ];

    public function studentViolations(): HasMany
    {
        return $this->hasMany(StudentViolation::class);
    }
}
