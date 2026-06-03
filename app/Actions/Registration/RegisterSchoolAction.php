<?php

namespace App\Actions\Registration;

use App\Actions\Catalog\SeedDefaultCatalogAction;
use App\Enums\SchoolStatus;
use App\Models\School;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RegisterSchoolAction
{
    /**
     * Tenant admin permissions granted to the school admin role on registration.
     * Kept in sync with RoleSeeder/PlatformSeeder.
     *
     * @var array<int, string>
     */
    private const ADMIN_PERMISSIONS = [
        'students.view', 'students.create', 'students.update', 'students.delete',
        'teachers.view', 'teachers.create', 'teachers.update', 'teachers.delete',
        'classes.manage', 'academic.manage', 'academic-calendar.manage',
        'attendance.students.input', 'attendance.students.correct',
        'attendance.teachers.input', 'attendance.teachers.correct',
        'violations.input', 'violations.validate', 'violations.manage-types',
        'character-points.view', 'character-points.input', 'character-points.manage-types',
        'reports.print',
        'guardians.view-dashboard', 'guardians.view-child-reports',
    ];

    public function __construct(
        private readonly SeedDefaultCatalogAction $seedCatalog,
    ) {}

    /**
     * Create a school, its admin user (unverified), admin role, trial subscription,
     * and the default catalog. Returns the freshly created admin user.
     *
     * @param  array{school_name: string, admin_name: string, admin_email: string, password: string, plan?: string, period?: string}  $data
     */
    public function execute(array $data): User
    {
        $trialDays = (int) config('platform.trial_days', 14);

        return DB::transaction(function () use ($data, $trialDays): User {
            $school = School::query()->create([
                'name' => $data['school_name'],
                'slug' => $this->uniqueSlug($data['school_name']),
                'email' => $data['admin_email'],
                'status' => SchoolStatus::Trial,
                'trial_ends_at' => now()->addDays($trialDays),
                'onboarding_step' => 0,
            ]);

            Subscription::query()->create([
                'school_id' => $school->id,
                'plan' => $data['plan'] ?? 'standar',
                'period' => $data['period'] ?? 'monthly',
                'starts_at' => now()->toDateString(),
                'status' => 'active',
            ]);

            $admin = User::query()->create([
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'password' => Hash::make($data['password']),
                'school_id' => $school->id,
            ]);

            $this->ensurePermissions();

            setPermissionsTeamId($school->id);
            $role = Role::query()->firstOrCreate([
                'name' => 'admin-sekolah',
                'guard_name' => 'web',
                'school_id' => $school->id,
            ]);
            $role->syncPermissions(self::ADMIN_PERMISSIONS);
            $admin->assignRole($role);

            $this->seedCatalog->execute($school);

            return $admin;
        });
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'sekolah';
        $slug = $base;
        $suffix = 1;

        while (School::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$suffix);
        }

        return $slug;
    }

    private function ensurePermissions(): void
    {
        foreach (self::ADMIN_PERMISSIONS as $permission) {
            Permission::query()->firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }
}
