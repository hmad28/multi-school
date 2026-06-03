<?php

namespace Tests\Feature\Registration;

use App\Enums\SchoolStatus;
use App\Models\CharacterPointType;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Models\ViolationType;
use App\Notifications\TrialEndingReminder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SchoolRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_registration_screen_renders(): void
    {
        $this->get('/daftar')->assertOk();
    }

    public function test_school_can_self_register(): void
    {
        Event::fake([Registered::class]);

        $response = $this->post('/daftar', [
            'school_name' => 'SD Harapan Bangsa',
            'admin_name' => 'Pak Budi',
            'admin_email' => 'budi@harapan.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'plan' => 'standar',
            'period' => 'monthly',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticated();

        $school = School::query()->where('slug', 'sd-harapan-bangsa')->first();
        $this->assertNotNull($school);
        $this->assertSame(SchoolStatus::Trial, $school->status);
        $this->assertNotNull($school->trial_ends_at);
        $this->assertNull($school->onboarding_completed_at);

        $admin = User::query()->where('email', 'budi@harapan.test')->first();
        $this->assertNotNull($admin);
        $this->assertSame($school->id, $admin->school_id);
        $this->assertNull($admin->email_verified_at);

        // Catalog seeded per-tenant
        $this->assertSame(9, ViolationType::query()->where('school_id', $school->id)->count());
        $this->assertSame(9, CharacterPointType::query()->where('school_id', $school->id)->count());

        // Subscription created
        $this->assertDatabaseHas('subscriptions', ['school_id' => $school->id, 'plan' => 'standar']);

        // Admin role with permissions
        setPermissionsTeamId($school->id);
        $this->assertTrue($admin->fresh()->hasRole('admin-sekolah'));
        $this->assertTrue($admin->can('students.create'));

        Event::assertDispatched(Registered::class);
    }

    public function test_registration_generates_unique_slug_on_collision(): void
    {
        $this->post('/daftar', [
            'school_name' => 'SD Mawar',
            'admin_name' => 'Admin Satu',
            'admin_email' => 'satu@mawar.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect();

        $this->post('/logout');

        $this->post('/daftar', [
            'school_name' => 'SD Mawar',
            'admin_name' => 'Admin Dua',
            'admin_email' => 'dua@mawar.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect();

        $this->assertTrue(School::query()->where('slug', 'sd-mawar')->exists());
        $this->assertTrue(School::query()->where('slug', 'sd-mawar-2')->exists());
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        $this->post('/daftar', [
            'school_name' => 'SD Satu',
            'admin_name' => 'Admin',
            'admin_email' => 'dup@test.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect();

        $this->post('/logout');

        $this->post('/daftar', [
            'school_name' => 'SD Dua',
            'admin_name' => 'Admin Lain',
            'admin_email' => 'dup@test.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('admin_email');
    }

    public function test_unverified_admin_cannot_reach_tenant_dashboard(): void
    {
        $admin = $this->registerSchool();
        $school = $admin->school;

        $this->actingAs($admin)
            ->get("/t/{$school->slug}/dashboard")
            ->assertRedirect(route('verification.notice'));
    }

    public function test_verified_admin_with_incomplete_onboarding_redirects_to_onboarding(): void
    {
        $admin = $this->registerSchool();
        $admin->markEmailAsVerified();
        $school = $admin->school;

        $this->actingAs($admin)
            ->get("/t/{$school->slug}/onboarding")
            ->assertOk();
    }

    public function test_admin_can_finish_onboarding(): void
    {
        $admin = $this->registerSchool();
        $admin->markEmailAsVerified();
        $school = $admin->school;

        $this->actingAs($admin)
            ->post("/t/{$school->slug}/onboarding/finish")
            ->assertRedirect("/t/{$school->slug}/dashboard");

        $this->assertNotNull($school->fresh()->onboarding_completed_at);
    }

    public function test_admin_can_update_profile_during_onboarding(): void
    {
        $admin = $this->registerSchool();
        $admin->markEmailAsVerified();
        $school = $admin->school;

        $this->actingAs($admin)
            ->patch("/t/{$school->slug}/onboarding/profile", [
                'phone' => '08123456789',
                'address' => 'Jl. Pendidikan No. 1',
                'principal_name' => 'Dr. Kepala',
            ])->assertRedirect();

        $school->refresh();
        $this->assertSame('08123456789', $school->phone);
        $this->assertSame('Dr. Kepala', $school->principal_name);
    }

    public function test_admin_can_invite_user_during_onboarding(): void
    {
        $admin = $this->registerSchool();
        $admin->markEmailAsVerified();
        $school = $admin->school;

        $this->actingAs($admin)
            ->post("/t/{$school->slug}/onboarding/invite", [
                'name' => 'Guru Baru',
                'email' => 'guru@harapan.test',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])->assertRedirect();

        $invited = User::query()->where('email', 'guru@harapan.test')->first();
        $this->assertNotNull($invited);
        $this->assertSame($school->id, $invited->school_id);
    }

    public function test_trial_reminder_command_notifies_and_expires(): void
    {
        Notification::fake();

        // Ending in 2 days → reminded
        $soon = $this->registerSchool('budi@soon.test', 'SD Soon');
        $soon->school->update(['trial_ends_at' => now()->addDays(2)]);

        // Already past → expired
        $past = $this->registerSchool('budi@past.test', 'SD Past');
        $past->school->update(['trial_ends_at' => now()->subDay()]);

        $this->artisan('platform:trial-reminders', ['--days' => 3])->assertSuccessful();

        Notification::assertSentTo($soon, TrialEndingReminder::class);
        $this->assertSame(SchoolStatus::Expired, $past->school->fresh()->status);
    }

    private function registerSchool(string $email = 'admin@harapan.test', string $name = 'SD Harapan'): User
    {
        $this->post('/daftar', [
            'school_name' => $name,
            'admin_name' => 'Admin',
            'admin_email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->post('/logout');

        return User::query()->where('email', $email)->firstOrFail();
    }
}
