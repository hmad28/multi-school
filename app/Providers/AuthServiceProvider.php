<?php

namespace App\Providers;

use App\Models\AcademicCalendarHoliday;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Policies\AcademicCalendarHolidayPolicy;
use App\Policies\SchoolClassPolicy;
use App\Policies\StudentPolicy;
use App\Policies\TeacherPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        AcademicCalendarHoliday::class => AcademicCalendarHolidayPolicy::class,
        SchoolClass::class => SchoolClassPolicy::class,
        Student::class => StudentPolicy::class,
        Teacher::class => TeacherPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
