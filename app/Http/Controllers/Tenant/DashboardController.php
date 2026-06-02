<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Teacher;
use App\Support\TenantContext;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $school = TenantContext::school();

        return Inertia::render('Dashboard', [
            'school' => $school?->only(['id', 'name', 'slug', 'status']),
            'tenantMode' => true,
            'metrics' => [
                'students' => Student::query()->where('status', 'active')->count(),
                'teachers' => Teacher::query()->where('status', 'active')->count(),
                'classes' => SchoolClass::query()->where('status', 'active')->count(),
                'academicYear' => AcademicYear::query()->where('is_active', true)->value('name'),
                'semester' => Semester::query()->where('is_active', true)->value('name'),
            ],
        ]);
    }
}
