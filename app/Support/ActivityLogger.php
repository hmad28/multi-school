<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\School;
use App\Models\User;

class ActivityLogger
{
    public static function log(
        string $action,
        string $description,
        ?User $user = null,
        ?School $school = null,
        array $metadata = [],
    ): ActivityLog {
        return ActivityLog::query()->create([
            'user_id' => $user?->id,
            'school_id' => $school?->id,
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    public static function logPasswordReset(User $admin, User $performedBy): ActivityLog
    {
        return static::log(
            action: 'password.reset',
            description: "Password admin {$admin->name} ({$admin->email}) direset oleh {$performedBy->name}.",
            user: $performedBy,
            school: $admin->school,
            metadata: [
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
            ],
        );
    }

    public static function logStatusChange(School $school, string $from, string $to, User $performedBy): ActivityLog
    {
        return static::log(
            action: "tenant.{$to}",
            description: "Status {$school->name} berubah dari {$from} menjadi {$to} oleh {$performedBy->name}.",
            user: $performedBy,
            school: $school,
            metadata: [
                'from' => $from,
                'to' => $to,
            ],
        );
    }
}
