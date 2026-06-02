<?php

namespace Tests\Feature\Tenant;

use App\Models\AcademicCalendarHoliday;
use App\Models\School;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicCalendarHolidayTest extends TestCase
{
    use RefreshDatabase;

    public function test_academic_calendar_holidays_are_scoped_to_current_tenant(): void
    {
        $demo = School::query()->create([
            'name' => 'Demo School',
            'slug' => 'demo',
            'email' => 'demo@example.test',
            'status' => 'active',
        ]);
        $alfa = School::query()->create([
            'name' => 'Alfa School',
            'slug' => 'alfa',
            'email' => 'alfa@example.test',
            'status' => 'active',
        ]);

        TenantContext::set($demo);
        AcademicCalendarHoliday::query()->create([
            'date' => '2026-06-01',
            'name' => 'Libur Demo',
            'description' => 'Rapat guru demo',
            'status' => 'active',
        ]);

        TenantContext::set($alfa);
        AcademicCalendarHoliday::query()->create([
            'date' => '2026-06-01',
            'name' => 'Libur Alfa',
            'description' => 'Rapat guru alfa',
            'status' => 'active',
        ]);

        $this->assertSame(['Libur Alfa'], AcademicCalendarHoliday::query()->pluck('name')->all());

        TenantContext::set($demo);
        $this->assertSame(['Libur Demo'], AcademicCalendarHoliday::query()->pluck('name')->all());
    }

    public function test_tenant_admin_can_manage_academic_calendar_holidays(): void
    {
        $this->seed();

        $school = School::query()->where('slug', 'demo')->firstOrFail();
        $admin = \App\Models\User::query()->where('email', 'admin@demo.test')->firstOrFail();

        $this->actingAs($admin)->get(route('tenant.academic-calendar.holidays.index', ['tenant' => $school->slug]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('AcademicCalendar/Holidays/Index'));

        $this->actingAs($admin)->post(route('tenant.academic-calendar.holidays.store', ['tenant' => $school->slug]), [
            'date' => '2026-06-01',
            'name' => 'Libur sekolah',
            'description' => 'Rapat guru',
            'status' => 'active',
        ])->assertRedirect(route('tenant.academic-calendar.holidays.index', ['tenant' => $school->slug]));

        $holiday = AcademicCalendarHoliday::query()->where('name', 'Libur sekolah')->firstOrFail();

        $this->actingAs($admin)->put(route('tenant.academic-calendar.holidays.update', ['tenant' => $school->slug, 'holiday' => $holiday->id]), [
            'date' => '2026-06-02',
            'name' => 'Libur nasional',
            'description' => null,
            'status' => 'inactive',
        ])->assertRedirect(route('tenant.academic-calendar.holidays.index', ['tenant' => $school->slug]));

        $this->assertDatabaseHas('academic_calendar_holidays', [
            'id' => $holiday->id,
            'school_id' => $school->id,
            'name' => 'Libur nasional',
            'status' => 'inactive',
        ]);
    }

    public function test_same_holiday_date_is_allowed_across_different_tenants(): void
    {
        $this->seed();

        $demo = School::query()->where('slug', 'demo')->firstOrFail();
        $alfa = School::query()->where('slug', 'alfa')->firstOrFail();
        $demoAdmin = \App\Models\User::query()->where('email', 'admin@demo.test')->firstOrFail();
        $alfaAdmin = \App\Models\User::query()->where('email', 'admin@alfa.test')->firstOrFail();

        $this->actingAs($demoAdmin)->post(route('tenant.academic-calendar.holidays.store', ['tenant' => $demo->slug]), [
            'date' => '2026-06-01',
            'name' => 'Libur demo',
            'description' => null,
            'status' => 'active',
        ])->assertRedirect();

        $this->actingAs($alfaAdmin)->post(route('tenant.academic-calendar.holidays.store', ['tenant' => $alfa->slug]), [
            'date' => '2026-06-01',
            'name' => 'Libur alfa',
            'description' => null,
            'status' => 'active',
        ])->assertRedirect();

        $this->assertDatabaseHas('academic_calendar_holidays', [
            'school_id' => $demo->id,
            'date' => '2026-06-01 00:00:00',
            'name' => 'Libur demo',
        ]);
        $this->assertDatabaseHas('academic_calendar_holidays', [
            'school_id' => $alfa->id,
            'date' => '2026-06-01 00:00:00',
            'name' => 'Libur alfa',
        ]);
    }
}

