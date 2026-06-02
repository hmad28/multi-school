<?php

namespace App\Enums;

enum SchoolStatus: string
{
    case Trial = 'trial';
    case Active = 'active';
    case Suspended = 'suspended';
    case Expired = 'expired';

    public function isAccessible(): bool
    {
        return in_array($this, [self::Trial, self::Active], true);
    }
}
