<?php

namespace Tests\Feature\Platform;

use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_user_cannot_view_student_from_another_school(): void
    {
        $this->seed();

        $alfaStudent = Student::query()
            ->withoutGlobalScopes()
            ->whereHas('school', fn ($q) => $q->where('slug', 'alfa'))
            ->firstOrFail();

        $demoAdmin = User::query()->where('email', 'admin@demo.test')->firstOrFail();

        $response = $this->actingAs($demoAdmin)->get(
            '/t/demo/students/'.$alfaStudent->id,
        );

        $response->assertNotFound();
    }

    public function test_tenant_dashboard_receives_school_shared_prop(): void
    {
        $this->seed();

        $demoAdmin = User::query()->where('email', 'admin@demo.test')->firstOrFail();

        $response = $this->actingAs($demoAdmin)->get('/t/demo/dashboard');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->where('school.slug', 'demo')
            ->where('school.name', 'SD Demo Platform')
            ->where('metrics.students', 1)
            ->where('metrics.teachers', 1)
            ->where('metrics.classes', 1)
            ->where('metrics.academicYear', '2026/2027')
            ->where('metrics.semester', 'Ganjil')
            ->where('appName', config('platform.name'))
            ->has('auth.user')
            ->has('auth.roles')
            ->has('auth.permissions'));
    }
}
