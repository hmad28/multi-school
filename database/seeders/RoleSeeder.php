<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::query()->firstOrCreate([
            'name' => 'super-admin',
            'guard_name' => 'web',
            'school_id' => null,
        ]);

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
            'reports.print',
            'guardians.view-dashboard',
            'guardians.view-child-reports',
        ];

        foreach ($permissions as $permission) {
            Permission::query()->firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        School::query()->each(function (School $school) use ($permissions): void {
            setPermissionsTeamId($school->id);

            $role = Role::query()->firstOrCreate([
                'name' => 'admin-sekolah',
                'guard_name' => 'web',
                'school_id' => $school->id,
            ]);

            $role->syncPermissions($permissions);
        });

        $guardianPermissions = ['guardians.view-dashboard', 'guardians.view-child-reports'];

        School::query()->each(function (School $school) use ($guardianPermissions): void {
            setPermissionsTeamId($school->id);

            $role = Role::query()->firstOrCreate([
                'name' => 'wali-murid',
                'guard_name' => 'web',
                'school_id' => $school->id,
            ]);

            $role->syncPermissions($guardianPermissions);
        });
    }
}
