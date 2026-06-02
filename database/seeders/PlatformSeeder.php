<?php

namespace Database\Seeders;

use App\Enums\SchoolStatus;
use App\Models\AcademicLevel;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subscription;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class PlatformSeeder extends Seeder
{
    public function run(): void
    {
        $trialDays = (int) config('platform.trial_days', 14);
        $permissions = [
            'students.view',
            'students.create',
            'students.update',
            'students.delete',
            'teachers.view',
            'teachers.create',
            'teachers.update',
            'teachers.delete',
            'classes.manage',
            'academic.manage',
            'academic-calendar.manage',
            'attendance.students.input',
            'attendance.students.correct',
            'attendance.teachers.input',
            'attendance.teachers.correct',
            'violations.input',
            'violations.validate',
            'violations.manage-types',
            'character-points.view',
            'character-points.input',
            'character-points.manage-types',
        ];

        $demo = School::query()->create([
            'name' => 'SD Demo Platform',
            'slug' => 'demo',
            'email' => 'admin@demo.test',
            'status' => SchoolStatus::Trial,
            'trial_ends_at' => now()->addDays($trialDays),
        ]);

        $alfa = School::query()->create([
            'name' => 'SD Alfa Nusantara',
            'slug' => 'alfa',
            'email' => 'admin@alfa.test',
            'status' => SchoolStatus::Active,
        ]);

        foreach ([$demo, $alfa] as $school) {
            Subscription::query()->create([
                'school_id' => $school->id,
                'plan' => 'standar',
                'period' => 'monthly',
                'starts_at' => now()->toDateString(),
                'status' => 'active',
                'amount' => 249000,
            ]);
        }

        $superAdmin = User::query()->create([
            'name' => 'Super Admin',
            'email' => 'super@platformsekolah.test',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);
        $superAdmin->assignRole('super-admin');

        $demoAdmin = User::query()->create([
            'name' => 'Admin Demo',
            'email' => 'admin@demo.test',
            'password' => 'password',
            'school_id' => $demo->id,
            'email_verified_at' => now(),
        ]);
        setPermissionsTeamId($demo->id);
        $demoRole = Role::query()->firstOrCreate([
            'name' => 'admin-sekolah',
            'guard_name' => 'web',
            'school_id' => $demo->id,
        ]);
        $demoRole->syncPermissions($permissions);
        $demoAdmin->assignRole($demoRole);

        $alfaAdmin = User::query()->create([
            'name' => 'Admin Alfa',
            'email' => 'admin@alfa.test',
            'password' => 'password',
            'school_id' => $alfa->id,
            'email_verified_at' => now(),
        ]);
        setPermissionsTeamId($alfa->id);
        $alfaRole = Role::query()->firstOrCreate([
            'name' => 'admin-sekolah',
            'guard_name' => 'web',
            'school_id' => $alfa->id,
        ]);
        $alfaRole->syncPermissions($permissions);
        $alfaAdmin->assignRole($alfaRole);

        $this->seedSchoolMasterData($demo, 'DEMO');
        $this->seedSchoolMasterData($alfa, 'ALFA');
    }

    private function seedSchoolMasterData(School $school, string $prefix): void
    {
        $year = AcademicYear::query()->create([
            'school_id' => $school->id,
            'name' => '2026/2027',
            'starts_on' => '2026-07-01',
            'ends_on' => '2027-06-30',
            'is_active' => true,
        ]);

        Semester::query()->create([
            'school_id' => $school->id,
            'academic_year_id' => $year->id,
            'name' => 'Ganjil',
            'starts_on' => '2026-07-01',
            'ends_on' => '2026-12-20',
            'is_active' => true,
        ]);

        Semester::query()->create([
            'school_id' => $school->id,
            'academic_year_id' => $year->id,
            'name' => 'Genap',
            'starts_on' => '2027-01-05',
            'ends_on' => '2027-06-30',
            'is_active' => false,
        ]);

        $level = AcademicLevel::query()->create([
            'school_id' => $school->id,
            'name' => 'Kelas 1',
            'numeric_value' => 1,
        ]);

        $teacher = Teacher::query()->create([
            'school_id' => $school->id,
            'nip' => "{$prefix}-GURU-001",
            'full_name' => "Guru {$prefix}",
            'position' => 'Wali Kelas',
            'phone' => '081234567890',
            'status' => 'active',
            'can_input_teacher_attendance' => true,
        ]);

        $class = SchoolClass::query()->create([
            'school_id' => $school->id,
            'academic_level_id' => $level->id,
            'homeroom_teacher_id' => $teacher->id,
            'name' => 'A',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        Student::query()->create([
            'school_id' => $school->id,
            'class_id' => $class->id,
            'name' => "Ahmad {$prefix}",
            'nis' => "{$prefix}-001",
            'nisn' => "{$prefix}-NISN-001",
            'gender' => 'male',
            'guardian_name' => "Wali {$prefix}",
            'guardian_phone' => '081234567891',
            'address' => 'Alamat demo sekolah',
            'status' => 'active',
        ]);

        $h = \App\Models\AttendanceStatus::query()->where('code', 'H')->first();
        if ($h) {
            \App\Models\StudentAttendance::query()->create([
                'school_id' => $school->id,
                'student_id' => Student::query()->where('school_id', $school->id)->firstOrFail()->id,
                'class_id' => $class->id,
                'attendance_status_id' => $h->id,
                'date' => today()->toDateString(),
                'locked_at' => now(),
            ]);
        }

        \App\Models\AcademicCalendarHoliday::query()->firstOrCreate([
            'school_id' => $school->id,
            'date' => now()->startOfMonth()->addDays(5)->toDateString(),
        ], [
            'name' => 'Libur sekolah',
            'description' => 'Contoh hari libur untuk tenant.',
            'status' => 'active',
        ]);

        \App\Models\AcademicCalendarHoliday::query()->firstOrCreate([
            'school_id' => $school->id,
            'date' => now()->startOfMonth()->addDays(12)->toDateString(),
        ], [
            'name' => 'Kegiatan guru',
            'description' => 'Contoh agenda non-aktif',
            'status' => 'inactive',
        ]);
    }
}
