<?php

namespace Tests\Feature\Platform;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SuperAdminCanListTenantsTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_open_tenant_list(): void
    {
        $this->seed();

        $super = User::query()->where('email', 'super@platformsekolah.test')->firstOrFail();

        $response = $this->actingAs($super)->get('/platform/tenants');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Platform/Tenants/Index')
            ->has('schools', 2));
    }

    public function test_tenant_admin_cannot_open_platform_tenants(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@demo.test')->firstOrFail();

        $response = $this->actingAs($admin)->get('/platform/tenants');

        $response->assertForbidden();
    }

    public function test_tenant_admin_cannot_login_to_platform(): void
    {
        $this->seed();

        $response = $this->post('/platform/login', [
            'email' => 'admin@demo.test',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_super_admin_resets_only_target_school_admin_password(): void
    {
        $this->seed();

        $super = User::query()->where('email', 'super@platformsekolah.test')->firstOrFail();
        $demoAdmin = User::query()->where('email', 'admin@demo.test')->firstOrFail();
        $alfaAdmin = User::query()->where('email', 'admin@alfa.test')->firstOrFail();
        $oldAlfaPassword = $alfaAdmin->password;

        $response = $this->actingAs($super)->post('/platform/tenants/'.$demoAdmin->school_id.'/reset-password');

        $response->assertRedirect();
        $this->assertNotSame($demoAdmin->password, $demoAdmin->fresh()->password);
        $this->assertSame($oldAlfaPassword, $alfaAdmin->fresh()->password);
    }
}
