<?php

namespace Tests\Feature\Platform;

use App\Models\ActivityLog;
use App\Models\School;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformAdminAuditTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private School $demo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->superAdmin = User::query()->where('email', 'super@platformsekolah.test')->firstOrFail();
        $this->demo = School::query()->where('slug', 'demo')->firstOrFail();
    }

    public function test_super_admin_can_view_activity_logs_on_tenant_detail(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('platform.tenants.show', $this->demo->id))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Platform/Tenants/Show')
                ->has('activityLogs')
            );
    }

    public function test_status_change_creates_activity_log(): void
    {
        $this->actingAs($this->superAdmin)
            ->patch(route('platform.tenants.status', $this->demo->id), ['status' => 'suspended']);

        $this->assertDatabaseHas('activity_logs', [
            'school_id' => $this->demo->id,
            'user_id' => $this->superAdmin->id,
            'action' => 'tenant.suspended',
        ]);
    }

    public function test_reset_password_creates_activity_log(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('platform.tenants.reset-password', $this->demo->id));

        $this->assertDatabaseHas('activity_logs', [
            'school_id' => $this->demo->id,
            'user_id' => $this->superAdmin->id,
            'action' => 'password.reset',
        ]);
    }

    public function test_activity_log_stores_metadata(): void
    {
        $this->actingAs($this->superAdmin)
            ->patch(route('platform.tenants.status', $this->demo->id), ['status' => 'suspended']);

        $log = ActivityLog::query()
            ->where('school_id', $this->demo->id)
            ->where('action', 'tenant.suspended')
            ->firstOrFail();

        $this->assertIsArray($log->metadata);
        $this->assertEquals('trial', $log->metadata['from']);
        $this->assertEquals('suspended', $log->metadata['to']);
    }

    public function test_activity_log_indexes_exist(): void
    {
        $this->actingAs($this->superAdmin)
            ->patch(route('platform.tenants.status', $this->demo->id), ['status' => 'active']);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'tenant.active',
            'school_id' => $this->demo->id,
        ]);
    }
}
