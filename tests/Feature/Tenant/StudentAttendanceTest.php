<?php

namespace Tests\Feature\Tenant;

use App\Models\AttendanceStatus;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Teacher;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private School $demo;
    private School $alfa;
    private User $demoAdmin;
    private User $alfaAdmin;
    private SchoolClass $demoClass;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->demo = School::query()->where('slug', 'demo')->firstOrFail();
        $this->alfa = School::query()->where('slug', 'alfa')->firstOrFail();
        $this->demoAdmin = User::query()->where('email', 'admin@demo.test')->firstOrFail();
        $this->alfaAdmin = User::query()->where('email', 'admin@alfa.test')->firstOrFail();

        $teacher = Teacher::query()->where('school_id', $this->demo->id)->first();
        $this->demoClass = SchoolClass::query()->where('school_id', $this->demo->id)->first();
    }

    public function test_attendance_index_page_loads(): void
    {
        $this->actingAs($this->demoAdmin)
            ->get(route('tenant.attendance.students.index', ['tenant' => $this->demo->slug]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Attendance/Students/Index'));
    }

    public function test_attendance_is_scoped_to_current_tenant(): void
    {
        $alfaStudent = Student::query()->where('school_id', $this->alfa->id)->first();
        $alfaClass = SchoolClass::query()->where('school_id', $this->alfa->id)->first();
        $h = AttendanceStatus::query()->where('code', 'H')->firstOrFail();

        TenantContext::set($this->alfa);
        StudentAttendance::query()->create([
            'student_id' => $alfaStudent->id,
            'class_id' => $alfaClass->id,
            'attendance_status_id' => $h->id,
            'date' => '2026-06-01',
        ]);
        TenantContext::clear();

        $this->actingAs($this->demoAdmin)
            ->get(route('tenant.attendance.students.index', ['tenant' => $this->demo->slug]))
            ->assertOk();

        $this->assertDatabaseMissing('student_attendances', [
            'school_id' => $this->demo->id,
            'date' => '2026-06-01',
        ]);
    }

    public function test_tenant_admin_can_submit_attendance(): void
    {
        $students = Student::query()->where('school_id', $this->demo->id)->get();
        $h = AttendanceStatus::query()->where('code', 'H')->firstOrFail();

        $attendances = $students->map(fn (Student $s) => [
            'student_id' => $s->id,
            'attendance_status_id' => $h->id,
            'note' => null,
        ])->all();

        $this->actingAs($this->demoAdmin)
            ->post(route('tenant.attendance.students.store', ['tenant' => $this->demo->slug]), [
                'class_id' => $this->demoClass->id,
                'date' => '2026-06-01',
                'attendances' => $attendances,
            ])->assertRedirect();

        $this->assertDatabaseHas('student_attendances', [
            'school_id' => $this->demo->id,
            'date' => '2026-06-01 00:00:00',
        ]);
    }

    public function test_duplicate_submission_is_rejected(): void
    {
        $students = Student::query()->where('school_id', $this->demo->id)->get();
        $h = AttendanceStatus::query()->where('code', 'H')->firstOrFail();

        $attendances = $students->map(fn (Student $s) => [
            'student_id' => $s->id,
            'attendance_status_id' => $h->id,
        ])->all();

        $this->actingAs($this->demoAdmin)
            ->post(route('tenant.attendance.students.store', ['tenant' => $this->demo->slug]), [
                'class_id' => $this->demoClass->id,
                'date' => '2026-06-01',
                'attendances' => $attendances,
            ])->assertRedirect();

        $this->actingAs($this->demoAdmin)
            ->post(route('tenant.attendance.students.store', ['tenant' => $this->demo->slug]), [
                'class_id' => $this->demoClass->id,
                'date' => '2026-06-01',
                'attendances' => $attendances,
            ])->assertSessionHasErrors(['date']);
    }

    public function test_tenant_admin_can_finalize_attendance(): void
    {
        $students = Student::query()->where('school_id', $this->demo->id)->get();
        $h = AttendanceStatus::query()->where('code', 'H')->firstOrFail();

        $attendances = $students->map(fn (Student $s) => [
            'student_id' => $s->id,
            'attendance_status_id' => $h->id,
        ])->all();

        $this->actingAs($this->demoAdmin)
            ->post(route('tenant.attendance.students.store', ['tenant' => $this->demo->slug]), [
                'class_id' => $this->demoClass->id,
                'date' => '2026-06-01',
                'attendances' => $attendances,
            ])->assertRedirect();

        $this->actingAs($this->demoAdmin)
            ->post(route('tenant.attendance.students.finalize', ['tenant' => $this->demo->slug]), [
                'class_id' => $this->demoClass->id,
                'date' => '2026-06-01',
            ])->assertSessionHasNoErrors();
    }

    public function test_cross_tenant_attendance_is_forbidden(): void
    {
        $alfaStudents = Student::query()->where('school_id', $this->alfa->id)->get();
        $alfaClass = SchoolClass::query()->where('school_id', $this->alfa->id)->first();
        $h = AttendanceStatus::query()->where('code', 'H')->firstOrFail();

        $this->actingAs($this->demoAdmin)
            ->post(route('tenant.attendance.students.store', ['tenant' => $this->alfa->slug]), [
                'class_id' => $alfaClass->id,
                'date' => '2026-06-01',
                'attendances' => $alfaStudents->map(fn (Student $s) => [
                    'student_id' => $s->id,
                    'attendance_status_id' => $h->id,
                ])->all(),
            ])->assertStatus(403);
    }

    public function test_recap_page_loads(): void
    {
        $this->actingAs($this->demoAdmin)
            ->get(route('tenant.attendance.students.recap', ['tenant' => $this->demo->slug]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Attendance/Students/Recap'));
    }
}
