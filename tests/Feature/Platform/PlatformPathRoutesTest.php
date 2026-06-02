<?php

namespace Tests\Feature\Platform;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformPathRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_login_is_reachable_on_path_without_domain(): void
    {
        $this->seed();

        $this->get('http://127.0.0.1/platform/login')
            ->assertOk();

        $this->get('http://localhost/platform/login')
            ->assertOk();
    }

    public function test_tenant_login_path_is_reachable(): void
    {
        $this->seed();

        $this->get('/t/demo/login')->assertOk();
    }

    public function test_platform_tenants_requires_auth(): void
    {
        $this->get('/platform/tenants')->assertRedirect();
    }

    public function test_tenant_login_redirects_to_tenant_dashboard(): void
    {
        $this->seed();

        $response = $this->post('/t/demo/login', [
            'email' => 'admin@demo.test',
            'password' => 'password',
        ]);

        $response->assertRedirect('/t/demo/dashboard');
    }

    public function test_tenant_register_route_is_not_available(): void
    {
        $this->seed();

        $this->get('/t/demo/register')->assertNotFound();
    }

    public function test_tenant_dashboard_renders_without_missing_route_parameters(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@demo.test')->firstOrFail();

        $this->actingAs($admin)->get('/t/demo/dashboard')->assertOk();
    }
}
