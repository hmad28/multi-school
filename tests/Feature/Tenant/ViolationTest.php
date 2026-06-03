<?php

namespace Tests\Feature\Tenant;

use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Models\ViolationType;
use App\Models\StudentViolation;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViolationTest extends TestCase
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

    public function test_violation_types_index_page_loads(): void
    {
        $this->actingAs($this->demoAdmin)
            ->get(route('tenant.violation-types.index', ['tenant' => $this->demo->slug]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Violations/Types/Index'));
    }

    public function test_violation_type_can_be_created(): void
    {
        $this->actingAs($this->demoAdmin)
            ->post(route('tenant.violation-types.store', ['tenant' => $this->demo->slug]), [
                'category' => 'ringan',
                'name' => 'Test violation',
                'points' => 5,
                'status' => 'active',
                'sort_order' => 99,
            ])->assertRedirect();

        $this->assertDatabaseHas('violation_types', [
            'name' => 'Test violation',
            'school_id' => $this->demo->id,
        ]);
    }

    public function test_violation_types_are_isolated_per_tenant(): void
    {
        $alfa = School::query()->where('slug', 'alfa')->firstOrFail();

        TenantContext::set($alfa);
        $alfaType = ViolationType::query()->firstOrFail();
        TenantContext::clear();

        $this->actingAs($this->demoAdmin)
            ->get(route('tenant.violation-types.index', ['tenant' => $this->demo->slug]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Violations/Types/Index')
                ->where('types.data', fn ($types) => collect($types)->every(
                    fn ($type) => $type['id'] !== $alfaType->id,
                )));
    }

    public function test_student_violation_page_loads(): void
    {
        $this->actingAs($this->demoAdmin)
            ->get(route('tenant.student-violations.index', ['tenant' => $this->demo->slug]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Violations/Students/Index'));
    }

    public function test_student_violation_can_be_created(): void
    {
        TenantContext::set($this->demo);
        $student = Student::query()->where('school_id', $this->demo->id)->firstOrFail();
        $type = ViolationType::query()->firstOrFail();

        $this->actingAs($this->demoAdmin)
            ->post(route('tenant.student-violations.store', ['tenant' => $this->demo->slug]), [
                'student_id' => $student->id,
                'violation_type_id' => $type->id,
                'date' => today()->toDateString(),
                'note' => 'Test violation note',
            ])->assertRedirect();

        $this->assertDatabaseHas('student_violations', [
            'school_id' => $this->demo->id,
            'student_id' => $student->id,
            'status' => 'pending',
        ]);
    }

    public function test_student_violation_can_be_validated(): void
    {
        TenantContext::set($this->demo);
        $student = Student::query()->where('school_id', $this->demo->id)->firstOrFail();
        $type = ViolationType::query()->firstOrFail();

        $this->actingAs($this->demoAdmin)
            ->post(route('tenant.student-violations.store', ['tenant' => $this->demo->slug]), [
                'student_id' => $student->id,
                'violation_type_id' => $type->id,
                'date' => today()->toDateString(),
            ]);

        $violation = StudentViolation::query()->where('school_id', $this->demo->id)->firstOrFail();
        $this->assertEquals('pending', $violation->status);

        $response = $this->actingAs($this->demoAdmin)
            ->patch(route('tenant.student-violations.validate', ['tenant' => $this->demo->slug, 'studentViolation' => $violation->id]));

        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $violation->refresh();
        $this->assertEquals('validated', $violation->status);
        $this->assertNotNull($violation->validated_by);
        $this->assertNotNull($violation->validated_at);
    }

    public function test_student_violation_can_be_rejected(): void
    {
        TenantContext::set($this->demo);
        $student = Student::query()->where('school_id', $this->demo->id)->firstOrFail();
        $type = ViolationType::query()->firstOrFail();

        $this->actingAs($this->demoAdmin)
            ->post(route('tenant.student-violations.store', ['tenant' => $this->demo->slug]), [
                'student_id' => $student->id,
                'violation_type_id' => $type->id,
                'date' => today()->toDateString(),
            ]);

        $violation = StudentViolation::query()->where('school_id', $this->demo->id)->firstOrFail();

        $response = $this->actingAs($this->demoAdmin)
            ->patch(route('tenant.student-violations.reject', ['tenant' => $this->demo->slug, 'studentViolation' => $violation->id]), [
                'rejection_reason' => 'Tidak cukup bukti',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $violation->refresh();
        $this->assertEquals('rejected', $violation->status);
        $this->assertNotNull($violation->validated_by);
        $this->assertNotNull($violation->validated_at);
    }

    public function test_pending_violation_page_loads(): void
    {
        $this->actingAs($this->demoAdmin)
            ->get(route('tenant.student-violations.pending', ['tenant' => $this->demo->slug]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Violations/Students/Pending'));
    }

    public function test_cross_tenant_student_violation_is_forbidden(): void
    {
        $alfa = School::query()->where('slug', 'alfa')->firstOrFail();
        TenantContext::set($alfa);

        $alfaStudent = Student::query()->where('school_id', $alfa->id)->firstOrFail();
        $type = ViolationType::query()->firstOrFail();

        TenantContext::set($this->demo);

        $this->actingAs($this->demoAdmin)
            ->post(route('tenant.student-violations.store', ['tenant' => $alfa->slug]), [
                'student_id' => $alfaStudent->id,
                'violation_type_id' => $type->id,
                'date' => today()->toDateString(),
            ])->assertStatus(403);
    }
}
