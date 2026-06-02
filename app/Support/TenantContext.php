<?php

namespace App\Support;

use App\Models\School;

class TenantContext
{
    protected static ?School $school = null;

    public static function set(School $school): void
    {
        static::$school = $school;
    }

    public static function school(): ?School
    {
        return static::$school;
    }

    public static function id(): ?string
    {
        return static::$school?->id;
    }

    public static function clear(): void
    {
        static::$school = null;
    }
}
