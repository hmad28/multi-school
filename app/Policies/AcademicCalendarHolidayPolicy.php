<?php

namespace App\Policies;

use App\Models\AcademicCalendarHoliday;
use App\Models\User;

class AcademicCalendarHolidayPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('academic-calendar.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('academic-calendar.manage');
    }

    public function update(User $user, AcademicCalendarHoliday $holiday): bool
    {
        return $user->can('academic-calendar.manage') && $user->school_id === $holiday->school_id;
    }

    public function delete(User $user, AcademicCalendarHoliday $holiday): bool
    {
        return $user->can('academic-calendar.manage') && $user->school_id === $holiday->school_id;
    }
}
