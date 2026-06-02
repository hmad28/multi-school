<?php

namespace App\Policies;

use App\Models\Teacher;
use App\Models\User;
use App\Support\TenantContext;

class TeacherPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->school_id === TenantContext::id() && $user->can('teachers.view');
    }

    public function create(User $user): bool
    {
        return $user->school_id === TenantContext::id() && $user->can('teachers.create');
    }

    public function update(User $user, Teacher $teacher): bool
    {
        return $teacher->school_id === TenantContext::id()
            && $user->school_id === TenantContext::id()
            && $user->can('teachers.update');
    }

    public function delete(User $user, Teacher $teacher): bool
    {
        return $teacher->school_id === TenantContext::id()
            && $user->school_id === TenantContext::id()
            && $user->can('teachers.delete');
    }
}
