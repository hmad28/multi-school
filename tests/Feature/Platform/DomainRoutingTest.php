<?php

namespace Tests\Feature\Platform;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainRoutingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_central_host_serves_platform_login_path(): void
    {
        $this->get('http://localhost/platform/login')->assertOk();
    }

    public function test_tenant_subdomain_serves_tenant_login(): void
    {
        if (! config('platform.use_subdomain_routing')) {
            $this->markTestSkipped('Subdomain routing disabled (PLATFORM_SUBDOMAIN_ROUTING=false).');
        }

        $base = config('platform.tenant_base_domain');

        $this->get("http://demo.{$base}/login")->assertOk();
    }

    public function test_admin_subdomain_redirects_guest_to_platform_login(): void
    {
        if (! config('platform.use_subdomain_routing')) {
            $this->markTestSkipped('Subdomain routing disabled (PLATFORM_SUBDOMAIN_ROUTING=false).');
        }

        $admin = config('platform.admin_domain');

        $this->get("http://{$admin}/")
            ->assertRedirect(route('platform.login'));
    }
}
