<?php

namespace Tests\Feature\Tenant;

use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuardianTest extends TestCase
{
    use RefreshDatabase;

    private School $demo;
    private User $waliDemo;
    private User $demoAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->demo = School::query()->where('slug', 'demo')->firstOrFail();
        $this->demoAdmin = User::query()->where('email', 'admin@demo.test')->firstOrFail();
        $this->waliDemo = User::query()->where('email', 'wali@demo.test')->firstOrFail();
    }

    public function test_wali_murid_can_view_guardian_dashboard(): void
    {
        $child = Student::query()->where('school_id', $this->demo->id)->firstOrFail();

        $this->actingAs($this->waliDemo)
            ->get(route('tenant.guardian.dashboard', ['tenant' => $this->demo->slug]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Guardian/Dashboard')
                ->has('children')
                ->where('children.0.full_name', $child->name)
                ->has('summary')
                ->has('latestAttendances')
                ->where('latestAttendances.0.student_name', $child->name)
                ->has('latestCharacterPoints')
                ->has('latestViolations')
            );
    }

    public function test_wali_murid_can_view_linked_child_report(): void
    {
        $student = Student::query()->where('school_id', $this->demo->id)->firstOrFail();

        $this->actingAs($this->waliDemo)
            ->get(route('tenant.guardian.students.show', [
                'tenant' => $this->demo->slug,
                'student' => $student->id,
            ]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Guardian/StudentShow')
                ->where('student.id', $student->id)
                ->has('attendances')
                ->has('violations')
                ->has('characterPoints')
            );
    }

    public function test_wali_murid_cannot_view_unlinked_child_report(): void
    {
        $otherSchool = School::query()->where('slug', 'alfa')->firstOrFail();
        $otherStudent = Student::query()->where('school_id', $otherSchool->id)->firstOrFail();

        $this->actingAs($this->waliDemo)
            ->get(route('tenant.guardian.students.show', [
                'tenant' => $this->demo->slug,
                'student' => $otherStudent->id,
            ]))
            ->assertNotFound();
    }

    public function test_unauthorized_user_cannot_view_guardian_dashboard(): void
    {
        $user = User::query()->create([
            'name' => 'No Role',
            'email' => 'norole@demo.test',
            'password' => 'password',
            'school_id' => $this->demo->id,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('tenant.guardian.dashboard', ['tenant' => $this->demo->slug]))
            ->assertForbidden();
    }

    public function test_admin_cannot_view_guardian_dashboard(): void
    {
        $noRoleUser = User::query()->create([
            'name' => 'No Role',
            'email' => 'norole2@demo.test',
            'password' => 'password',
            'school_id' => $this->demo->id,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($noRoleUser)
            ->get(route('tenant.guardian.dashboard', ['tenant' => $this->demo->slug]))
            ->assertForbidden();
    }
}
