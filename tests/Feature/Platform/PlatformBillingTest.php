<?php

namespace Tests\Feature\Platform;

use App\Models\School;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformBillingTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $normalUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->superAdmin = User::query()->where('email', 'super@platformsekolah.test')->firstOrFail();
        $this->normalUser = User::query()->where('email', 'admin@demo.test')->firstOrFail();
    }

    public function test_super_admin_can_view_billing_page(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('platform.billing.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Platform/Billing/Index')
                ->has('subscriptions')
            );
    }

    public function test_non_super_admin_cannot_view_billing_page(): void
    {
        $this->actingAs($this->normalUser)
            ->get(route('platform.billing.index'))
            ->assertForbidden();
    }

    public function test_billing_page_lists_all_subscriptions(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('platform.billing.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Platform/Billing/Index')
                ->has('subscriptions', 2) // demo + alfa
            );
    }

    public function test_billing_page_can_filter_by_status(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('platform.billing.index', ['status' => 'active']))
            ->assertOk();
    }

    public function test_billing_page_can_filter_by_plan(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('platform.billing.index', ['plan' => 'standar']))
            ->assertOk();
    }

    public function test_super_admin_can_update_subscription_status(): void
    {
        $school = School::query()->where('slug', 'demo')->firstOrFail();
        $subscription = $school->subscriptions()->firstOrFail();

        $this->actingAs($this->superAdmin)
            ->patch(route('platform.billing.status', $subscription->id), ['status' => 'past_due'])
            ->assertSessionHas('success');

        $subscription->refresh();
        $this->assertEquals('past_due', $subscription->status);
    }

    public function test_non_super_admin_cannot_update_subscription_status(): void
    {
        $school = School::query()->where('slug', 'demo')->firstOrFail();
        $subscription = $school->subscriptions()->firstOrFail();

        $this->actingAs($this->normalUser)
            ->patch(route('platform.billing.status', $subscription->id), ['status' => 'past_due'])
            ->assertForbidden();
    }

    public function test_billing_page_shows_correct_subscription_data(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('platform.billing.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Platform/Billing/Index')
                ->has('subscriptions', 2)
                ->has('subscriptions.0.school_name')
                ->has('subscriptions.0.plan')
                ->has('subscriptions.0.status')
                ->has('subscriptions.0.amount')
            );
    }
}
