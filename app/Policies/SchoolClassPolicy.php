<?php

namespace App\Policies;

use App\Models\SchoolClass;
use App\Models\User;
use App\Support\TenantContext;

class SchoolClassPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->school_id === TenantContext::id() && $user->can('classes.manage');
    }

    public function create(User $user): bool
    {
        return $user->school_id === TenantContext::id() && $user->can('classes.manage');
    }

    public function update(User $user, SchoolClass $schoolClass): bool
    {
        return $schoolClass->school_id === TenantContext::id()
            && $user->school_id === TenantContext::id()
            && $user->can('classes.manage');
    }

    public function delete(User $user, SchoolClass $schoolClass): bool
    {
        return $schoolClass->school_id === TenantContext::id()
            && $user->school_id === TenantContext::id()
            && $user->can('classes.manage');
    }
}
