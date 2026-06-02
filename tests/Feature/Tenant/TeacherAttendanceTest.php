<?php

namespace Tests\Feature\Tenant;

use App\Models\AttendanceStatus;
use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private School $demo;
    private User $demoAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->demo = School::query()->where('slug', 'demo')->firstOrFail();
        $this->demoAdmin = User::query()->where('email', 'admin@demo.test')->firstOrFail();
    }

    public function test_teacher_attendance_index_page_loads(): void
    {
        $this->actingAs($this->demoAdmin)
            ->get(route('tenant.attendance.teachers.index', ['tenant' => $this->demo->slug]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Attendance/Teachers/Index'));
    }

    public function test_tenant_admin_can_submit_teacher_attendance(): void
    {
        $teachers = Teacher::query()->where('school_id', $this->demo->id)->get();
        $h = AttendanceStatus::query()->where('code', 'H')->firstOrFail();

        $attendances = $teachers->map(fn (Teacher $t) => [
            'teacher_id' => $t->id,
            'attendance_status_id' => $h->id,
            'note' => null,
        ])->all();

        $this->actingAs($this->demoAdmin)
            ->post(route('tenant.attendance.teachers.store', ['tenant' => $this->demo->slug]), [
                'date' => today()->toDateString(),
                'attendances' => $attendances,
            ])->assertRedirect();

        $this->assertDatabaseHas('teacher_attendances', [
            'school_id' => $this->demo->id,
        ]);
    }

    public function test_duplicate_teacher_submission_is_rejected(): void
    {
        $teachers = Teacher::query()->where('school_id', $this->demo->id)->get();
        $h = AttendanceStatus::query()->where('code', 'H')->firstOrFail();

        $attendances = $teachers->map(fn (Teacher $t) => [
            'teacher_id' => $t->id,
            'attendance_status_id' => $h->id,
        ])->all();

        $this->actingAs($this->demoAdmin)
            ->post(route('tenant.attendance.teachers.store', ['tenant' => $this->demo->slug]), [
                'date' => today()->toDateString(),
                'attendances' => $attendances,
            ])->assertRedirect();

        $this->actingAs($this->demoAdmin)
            ->post(route('tenant.attendance.teachers.store', ['tenant' => $this->demo->slug]), [
                'date' => today()->toDateString(),
                'attendances' => $attendances,
            ])->assertSessionHasErrors(['date']);
    }

    public function test_cross_tenant_teacher_attendance_is_forbidden(): void
    {
        $alfa = School::query()->where('slug', 'alfa')->firstOrFail();
        $alfaTeachers = Teacher::query()->where('school_id', $alfa->id)->get();
        $h = AttendanceStatus::query()->where('code', 'H')->firstOrFail();

        $this->actingAs($this->demoAdmin)
            ->post(route('tenant.attendance.teachers.store', ['tenant' => $alfa->slug]), [
                'date' => today()->toDateString(),
                'attendances' => $alfaTeachers->map(fn (Teacher $t) => [
                    'teacher_id' => $t->id,
                    'attendance_status_id' => $h->id,
                ])->all(),
            ])->assertStatus(403);
    }
}
