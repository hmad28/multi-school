<?php

namespace Tests\Feature\Tenant;

use App\Models\School;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private School $demo;
    private User $demoAdmin;
    private User $demoUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->demo = School::query()->where('slug', 'demo')->firstOrFail();
        $this->demoAdmin = User::query()->where('email', 'admin@demo.test')->firstOrFail();

        $this->demoUser = User::query()->create([
            'name' => 'Demo User',
            'email' => 'user@demo.test',
            'password' => 'password',
            'school_id' => $this->demo->id,
            'email_verified_at' => now(),
        ]);
    }

    public function test_report_index_page_loads(): void
    {
        $this->actingAs($this->demoAdmin)
            ->get(route('tenant.reports.index', ['tenant' => $this->demo->slug]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Reports/Index'));
    }

    public function test_student_attendance_pdf_downloads(): void
    {
        $this->actingAs($this->demoAdmin)
            ->get(route('tenant.reports.student-attendance', [
                'tenant' => $this->demo->slug,
                'from' => today()->startOfMonth()->toDateString(),
                'to' => today()->toDateString(),
            ]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_student_attendance_excel_downloads(): void
    {
        $this->actingAs($this->demoAdmin)
            ->get(route('tenant.reports.student-attendance.excel', [
                'tenant' => $this->demo->slug,
                'from' => today()->startOfMonth()->toDateString(),
                'to' => today()->toDateString(),
            ]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_teacher_attendance_pdf_downloads(): void
    {
        $this->actingAs($this->demoAdmin)
            ->get(route('tenant.reports.teacher-attendance', [
                'tenant' => $this->demo->slug,
                'from' => today()->startOfMonth()->toDateString(),
                'to' => today()->toDateString(),
            ]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_teacher_attendance_excel_downloads(): void
    {
        $this->actingAs($this->demoAdmin)
            ->get(route('tenant.reports.teacher-attendance.excel', [
                'tenant' => $this->demo->slug,
                'from' => today()->startOfMonth()->toDateString(),
                'to' => today()->toDateString(),
            ]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_violations_pdf_downloads(): void
    {
        $this->actingAs($this->demoAdmin)
            ->get(route('tenant.reports.violations', [
                'tenant' => $this->demo->slug,
                'from' => today()->startOfMonth()->toDateString(),
                'to' => today()->toDateString(),
            ]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_violations_excel_downloads(): void
    {
        $this->actingAs($this->demoAdmin)
            ->get(route('tenant.reports.violations.excel', [
                'tenant' => $this->demo->slug,
                'from' => today()->startOfMonth()->toDateString(),
                'to' => today()->toDateString(),
            ]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_character_points_excel_downloads(): void
    {
        $this->actingAs($this->demoAdmin)
            ->get(route('tenant.reports.character-points.excel', [
                'tenant' => $this->demo->slug,
                'from' => today()->startOfMonth()->toDateString(),
                'to' => today()->toDateString(),
            ]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_parent_call_letter_pdf_downloads(): void
    {
        $student = \App\Models\Student::query()->where('school_id', $this->demo->id)->firstOrFail();

        $this->actingAs($this->demoAdmin)
            ->get(route('tenant.reports.parent-call-letter', [
                'tenant' => $this->demo->slug,
                'student_id' => $student->id,
            ]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_unauthorized_user_cannot_download_reports(): void
    {
        $this->actingAs($this->demoUser)
            ->get(route('tenant.reports.student-attendance', [
                'tenant' => $this->demo->slug,
                'from' => today()->startOfMonth()->toDateString(),
                'to' => today()->toDateString(),
            ]))
            ->assertForbidden();
    }

    public function test_report_page_requires_authentication(): void
    {
        $this->get(route('tenant.reports.index', ['tenant' => $this->demo->slug]))
            ->assertRedirect();
    }
}
