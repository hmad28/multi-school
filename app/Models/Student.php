<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use BelongsToSchool;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $appends = ['has_qr_token'];

    protected $hidden = ['qr_token', 'qr_token_hash'];

    protected $fillable = [
        'school_id',
        'name',
        'nis',
        'nisn',
        'class_id',
        'gender',
        'guardian_name',
        'guardian_phone',
        'address',
        'status',
        'qr_token',
        'qr_token_hash',
    ];

    protected function qrToken(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? decrypt($value) : null,
            set: fn (?string $value) => $value ? encrypt($value) : null,
        );
    }

    protected function hasQrToken(): Attribute
    {
        return Attribute::get(fn (): bool => filled($this->qr_token_hash));
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}
