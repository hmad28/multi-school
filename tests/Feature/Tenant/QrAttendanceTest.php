<?php

namespace Tests\Feature\Tenant;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private School $demo;
    private User $demoAdmin;
    private SchoolClass $demoClass;
    private Student $demoStudent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->demo = School::query()->where('slug', 'demo')->firstOrFail();
        $this->demoAdmin = User::query()->where('email', 'admin@demo.test')->firstOrFail();

        TenantContext::set($this->demo);
        $this->demoClass = SchoolClass::query()->where('school_id', $this->demo->id)->first();
        $this->demoStudent = Student::query()->where('school_id', $this->demo->id)->first();
        TenantContext::clear();
    }

    public function test_qr_scanner_page_loads(): void
    {
        $this->actingAs($this->demoAdmin)
            ->get(route('tenant.attendance.students.qr.index', ['tenant' => $this->demo->slug]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Attendance/Students/QrScanner'));
    }

    public function test_issue_student_token(): void
    {
        $qrRoute = route('tenant.attendance.students.qr.token', [
            'tenant' => $this->demo->slug,
            'student' => $this->demoStudent->id,
        ]);

        $this->actingAs($this->demoAdmin)
            ->get($qrRoute)
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Attendance/Students/QrToken'));
    }

    public function test_scan_via_qr_creates_attendance(): void
    {
        TenantContext::set($this->demo);
        $token = app(\App\Services\QrAttendanceService::class)->issueStudentToken($this->demoStudent);
        TenantContext::clear();

        $this->actingAs($this->demoAdmin)
            ->post(route('tenant.attendance.students.qr.scan', ['tenant' => $this->demo->slug]), [
                'student_token' => $token,
                'date' => today()->toDateString(),
                'scan_type' => 'arrival',
            ])->assertRedirect();

        $this->assertDatabaseHas('student_attendances', [
            'student_id' => $this->demoStudent->id,
            'arrival_source' => 'qr',
        ]);
    }

    public function test_scan_with_invalid_token_returns_error(): void
    {
        $this->actingAs($this->demoAdmin)
            ->post(route('tenant.attendance.students.qr.scan', ['tenant' => $this->demo->slug]), [
                'student_token' => 'invalid:token',
                'date' => today()->toDateString(),
                'scan_type' => 'arrival',
            ])->assertRedirect()
            ->assertSessionHasErrors(['student_token']);
    }

    public function test_regenerate_qr_updates_token(): void
    {
        TenantContext::set($this->demo);
        $oldHash = $this->demoStudent->qr_token_hash;
        TenantContext::clear();

        $this->actingAs($this->demoAdmin)
            ->post(route('tenant.attendance.students.qr.token.regenerate', [
                'tenant' => $this->demo->slug,
                'student' => $this->demoStudent->id,
            ]))->assertRedirect();

        $this->demoStudent->refresh();
        $this->assertNotSame($oldHash, $this->demoStudent->qr_token_hash);
    }
}
