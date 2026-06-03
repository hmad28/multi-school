<?php

namespace Tests\Feature\Tenant;

use App\Models\AcademicLevel;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    private School $demo;

    private School $alfa;

    private User $demoAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->demo = School::query()->where('slug', 'demo')->firstOrFail();
        $this->alfa = School::query()->where('slug', 'alfa')->firstOrFail();
        $this->demoAdmin = User::query()->where('email', 'admin@demo.test')->firstOrFail();
    }

    private function tenant(array $extra = []): array
    {
        return ['tenant' => $this->demo->slug] + $extra;
    }

    private function alfaStudent(): Student
    {
        TenantContext::set($this->alfa);
        $student = Student::query()->where('school_id', $this->alfa->id)->firstOrFail();
        TenantContext::clear();

        return $student;
    }

    // --- Students ------------------------------------------------------------

    public function test_student_index_loads(): void
    {
        $this->actingAs($this->demoAdmin)
            ->get(route('tenant.students.index', $this->tenant()))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Students/Index'));
    }

    public function test_student_can_be_created(): void
    {
        $this->actingAs($this->demoAdmin)
            ->post(route('tenant.students.store', $this->tenant()), [
                'name' => 'Budi Santoso',
                'nis' => 'NIS-NEW-1',
                'gender' => 'male',
                'status' => 'active',
            ])->assertRedirect();

        $this->assertDatabaseHas('students', [
            'name' => 'Budi Santoso',
            'nis' => 'NIS-NEW-1',
            'school_id' => $this->demo->id,
        ]);
    }

    public function test_student_requires_name_and_nis(): void
    {
        $this->actingAs($this->demoAdmin)
            ->post(route('tenant.students.store', $this->tenant()), [
                'status' => 'active',
            ])->assertSessionHasErrors(['name', 'nis']);
    }

    public function test_student_nis_unique_per_school_only(): void
    {
        // demo already has a seeded student with nis DEMO-001
        $existing = Student::query()->where('school_id', $this->demo->id)->firstOrFail();

        $this->actingAs($this->demoAdmin)
            ->post(route('tenant.students.store', $this->tenant()), [
                'name' => 'Duplikat',
                'nis' => $existing->nis,
                'status' => 'active',
            ])->assertSessionHasErrors('nis');
    }

    public function test_cannot_view_student_from_another_school(): void
    {
        $this->actingAs($this->demoAdmin)
            ->get(route('tenant.students.show', $this->tenant(['student' => $this->alfaStudent()->id])))
            ->assertNotFound();
    }

    public function test_cannot_update_student_from_another_school(): void
    {
        $this->actingAs($this->demoAdmin)
            ->put(route('tenant.students.update', $this->tenant(['student' => $this->alfaStudent()->id])), [
                'name' => 'Hijacked',
                'nis' => 'X-1',
                'status' => 'active',
            ])->assertNotFound();
    }

    // --- Teachers ------------------------------------------------------------

    public function test_teacher_index_loads(): void
    {
        $this->actingAs($this->demoAdmin)
            ->get(route('tenant.teachers.index', $this->tenant()))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Teachers/Index'));
    }

    public function test_teacher_can_be_created(): void
    {
        $this->actingAs($this->demoAdmin)
            ->post(route('tenant.teachers.store', $this->tenant()), [
                'full_name' => 'Ibu Sinta',
                'position' => 'Wali Kelas',
                'status' => 'active',
            ])->assertRedirect();

        $this->assertDatabaseHas('teachers', [
            'full_name' => 'Ibu Sinta',
            'school_id' => $this->demo->id,
        ]);
    }

    public function test_teacher_requires_full_name_and_position(): void
    {
        $this->actingAs($this->demoAdmin)
            ->post(route('tenant.teachers.store', $this->tenant()), [
                'status' => 'active',
            ])->assertSessionHasErrors(['full_name', 'position']);
    }

    public function test_cannot_delete_teacher_from_another_school(): void
    {
        TenantContext::set($this->alfa);
        $alfaTeacher = Teacher::query()->where('school_id', $this->alfa->id)->firstOrFail();
        TenantContext::clear();

        $this->actingAs($this->demoAdmin)
            ->delete(route('tenant.teachers.destroy', $this->tenant(['teacher' => $alfaTeacher->id])))
            ->assertForbidden();

        $this->assertDatabaseHas('teachers', ['id' => $alfaTeacher->id]);
    }

    // --- Classes -------------------------------------------------------------

    public function test_class_index_loads(): void
    {
        $this->actingAs($this->demoAdmin)
            ->get(route('tenant.classes.index', $this->tenant()))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Classes/Index'));
    }

    public function test_class_can_be_created(): void
    {
        TenantContext::set($this->demo);
        $level = AcademicLevel::query()->where('school_id', $this->demo->id)->firstOrFail();
        TenantContext::clear();

        $this->actingAs($this->demoAdmin)
            ->post(route('tenant.classes.store', $this->tenant()), [
                'academic_level_id' => $level->id,
                'name' => 'B',
                'status' => 'active',
                'sort_order' => 2,
            ])->assertRedirect();

        $this->assertDatabaseHas('classes', [
            'name' => 'B',
            'school_id' => $this->demo->id,
        ]);
    }

    public function test_class_rejects_academic_level_from_another_school(): void
    {
        TenantContext::set($this->alfa);
        $alfaLevel = AcademicLevel::query()->where('school_id', $this->alfa->id)->firstOrFail();
        TenantContext::clear();

        $this->actingAs($this->demoAdmin)
            ->post(route('tenant.classes.store', $this->tenant()), [
                'academic_level_id' => $alfaLevel->id,
                'name' => 'Lintas',
                'status' => 'active',
            ])->assertSessionHasErrors('academic_level_id');
    }

    public function test_cannot_delete_class_from_another_school(): void
    {
        TenantContext::set($this->alfa);
        $alfaClass = SchoolClass::query()->where('school_id', $this->alfa->id)->firstOrFail();
        TenantContext::clear();

        $this->actingAs($this->demoAdmin)
            ->delete(route('tenant.classes.destroy', $this->tenant(['class' => $alfaClass->id])))
            ->assertForbidden();
    }

    // --- Academic setup ------------------------------------------------------

    public function test_academic_index_loads(): void
    {
        $this->actingAs($this->demoAdmin)
            ->get(route('tenant.academic.index', $this->tenant()))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Academic/Index'));
    }

    public function test_academic_level_can_be_created(): void
    {
        $this->actingAs($this->demoAdmin)
            ->post(route('tenant.academic.levels.store', $this->tenant()), [
                'name' => 'Kelas 2',
                'numeric_value' => 2,
            ])->assertRedirect();

        $this->assertDatabaseHas('academic_levels', [
            'name' => 'Kelas 2',
            'school_id' => $this->demo->id,
        ]);
    }

    public function test_academic_level_numeric_value_unique_per_school(): void
    {
        // demo seeded with "Kelas 1" numeric_value 1
        $this->actingAs($this->demoAdmin)
            ->post(route('tenant.academic.levels.store', $this->tenant()), [
                'name' => 'Kelas Satu Lagi',
                'numeric_value' => 1,
            ])->assertSessionHasErrors('numeric_value');
    }
}
