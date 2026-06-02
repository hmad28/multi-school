<?php

namespace App\Policies;

use App\Models\User;
use App\Support\TenantContext;

class AcademicPolicy
{
    public function manage(User $user): bool
    {
        return $user->school_id === TenantContext::id() && $user->can('academic.manage');
    }
}
