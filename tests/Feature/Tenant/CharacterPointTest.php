<?php

namespace Tests\Feature\Tenant;

use App\Models\CharacterPointType;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CharacterPointTest extends TestCase
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

    public function test_character_point_types_index_page_loads(): void
    {
        $this->actingAs($this->demoAdmin)
            ->get(route('tenant.character-point-types.index', ['tenant' => $this->demo->slug]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('CharacterPoints/Types/Index'));
    }

    public function test_character_point_type_can_be_created(): void
    {
        $this->actingAs($this->demoAdmin)
            ->post(route('tenant.character-point-types.store', ['tenant' => $this->demo->slug]), [
                'category' => 'test',
                'name' => 'Test poin',
                'points' => 5,
                'status' => 'active',
                'sort_order' => 99,
            ])->assertRedirect();

        $this->assertDatabaseHas('character_point_types', ['name' => 'Test poin']);
    }

    public function test_student_character_point_page_loads(): void
    {
        $this->actingAs($this->demoAdmin)
            ->get(route('tenant.student-character-points.index', ['tenant' => $this->demo->slug]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('CharacterPoints/Students/Index'));
    }

    public function test_student_character_point_can_be_created(): void
    {
        TenantContext::set($this->demo);
        $student = Student::query()->where('school_id', $this->demo->id)->firstOrFail();
        $type = CharacterPointType::query()->firstOrFail();

        $this->actingAs($this->demoAdmin)
            ->post(route('tenant.student-character-points.store', ['tenant' => $this->demo->slug]), [
                'student_id' => $student->id,
                'character_point_type_id' => $type->id,
                'date' => today()->toDateString(),
                'note' => 'Test character point',
            ])->assertRedirect();

        $this->assertDatabaseHas('student_character_points', [
            'school_id' => $this->demo->id,
            'student_id' => $student->id,
        ]);
    }

    public function test_cross_tenant_character_point_is_forbidden(): void
    {
        $alfa = School::query()->where('slug', 'alfa')->firstOrFail();
        TenantContext::set($alfa);
        $alfaStudent = Student::query()->where('school_id', $alfa->id)->firstOrFail();
        $type = CharacterPointType::query()->firstOrFail();

        TenantContext::set($this->demo);

        $this->actingAs($this->demoAdmin)
            ->post(route('tenant.student-character-points.store', ['tenant' => $alfa->slug]), [
                'student_id' => $alfaStudent->id,
                'character_point_type_id' => $type->id,
                'date' => today()->toDateString(),
            ])->assertStatus(403);
    }
}
