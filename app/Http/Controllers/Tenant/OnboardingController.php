<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Support\ActivityLogger;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    public function show(): Response
    {
        $school = TenantContext::school();
        $this->authorizeAdmin();

        return Inertia::render('Onboarding/Index', [
            'school' => $school->only(['id', 'name', 'slug', 'email', 'phone', 'address', 'principal_name']),
            'steps' => $this->steps($school),
            'completed' => $school->hasCompletedOnboarding(),
            'trialEndsAt' => $school->trial_ends_at?->toIso8601String(),
        ]);
    }

    public function finish(): RedirectResponse
    {
        $school = TenantContext::school();
        $this->authorizeAdmin();

        if (! $school->hasCompletedOnboarding()) {
            $school->update(['onboarding_completed_at' => now()]);
        }

        return redirect()
            ->route('tenant.dashboard', ['tenant' => $school->slug])
            ->with('success', 'Onboarding selesai. Selamat datang di Platform Sekolah!');
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $school = TenantContext::school();
        $this->authorizeAdmin();

        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:500'],
            'principal_name' => ['required', 'string', 'max:100'],
        ]);

        $school->update($validated);

        return redirect()
            ->route('tenant.onboarding.show', ['tenant' => $school->slug])
            ->with('success', 'Profil sekolah diperbarui.');
    }

    public function invite(Request $request): RedirectResponse
    {
        $school = TenantContext::school();
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'school_id' => $school->id,
        ]);

        setPermissionsTeamId($school->id);
        $user->assignRole('admin-sekolah');

        ActivityLogger::log(
            'tenant.user_invited',
            "Pengguna {$user->name} ({$user->email}) ditambahkan ke {$school->name}.",
            auth()->user(),
            $school,
            ['invited_user_id' => $user->id],
        );

        return redirect()
            ->route('tenant.onboarding.show', ['tenant' => $school->slug])
            ->with('success', "Pengguna {$user->name} ditambahkan sebagai admin sekolah.");
    }

    /**
     * Compute per-step completion from real tenant data (soft gate).
     *
     * @return array<int, array<string, mixed>>
     */
    private function steps(\App\Models\School $school): array
    {
        $hasProfile = filled($school->phone) && filled($school->address) && filled($school->principal_name);
        $hasYear = AcademicYear::query()->where('is_active', true)->exists();
        $hasClass = SchoolClass::query()->where('status', 'active')->exists();
        $hasStudent = Student::query()->exists();

        return [
            [
                'key' => 'profile',
                'title' => 'Lengkapi profil sekolah',
                'description' => 'Telepon, alamat, dan nama kepala sekolah untuk kop laporan.',
                'done' => $hasProfile,
                'route' => 'tenant.onboarding.show',
            ],
            [
                'key' => 'academic',
                'title' => 'Tahun ajaran & semester aktif',
                'description' => 'Tetapkan tahun ajaran dan semester yang sedang berjalan.',
                'done' => $hasYear,
                'route' => 'tenant.academic.index',
            ],
            [
                'key' => 'classes',
                'title' => 'Buat kelas',
                'description' => 'Tambahkan minimal satu kelas aktif.',
                'done' => $hasClass,
                'route' => 'tenant.classes.index',
            ],
            [
                'key' => 'students',
                'title' => 'Tambah siswa',
                'description' => 'Daftarkan siswa, satu per satu atau import nanti.',
                'done' => $hasStudent,
                'route' => 'tenant.students.index',
            ],
            [
                'key' => 'invite',
                'title' => 'Undang pengguna',
                'description' => 'Tambahkan guru/operator lain (opsional, bisa nanti).',
                'done' => $school->users()->count() > 1,
                'route' => 'tenant.onboarding.show',
            ],
        ];
    }

    private function authorizeAdmin(): void
    {
        abort_unless(
            auth()->user()?->school_id === TenantContext::id()
                && auth()->user()->can('academic.manage'),
            403,
        );
    }
}
