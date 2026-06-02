<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;
use App\Support\TenantContext;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->school_id === TenantContext::id() && $user->can('students.view');
    }

    public function view(User $user, Student $student): bool
    {
        return $student->school_id === TenantContext::id()
            && $user->school_id === TenantContext::id()
            && $user->can('students.view');
    }

    public function create(User $user): bool
    {
        return $user->school_id === TenantContext::id() && $user->can('students.create');
    }

    public function update(User $user, Student $student): bool
    {
        return $student->school_id === TenantContext::id()
            && $user->school_id === TenantContext::id()
            && $user->can('students.update');
    }

    public function delete(User $user, Student $student): bool
    {
        return $student->school_id === TenantContext::id()
            && $user->school_id === TenantContext::id()
            && $user->can('students.delete');
    }
}
