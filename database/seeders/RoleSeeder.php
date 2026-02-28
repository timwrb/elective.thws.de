<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Seed super admin users who can access and configure everything.
     */
    public function run(): void
    {
        Artisan::call('shield:generate', [
            '--all' => true,
            '--panel' => 'admin',
            '--option' => 'permissions',
        ]);
        resolve(PermissionRegistrar::class)->forgetCachedPermissions();

        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $professorRole = Role::firstOrCreate(['name' => 'professor', 'guard_name' => 'web']);
        $studentRole = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        $superAdminRole->syncPermissions(Permission::all());
        $professorRole->syncPermissions(Permission::all());

        $daniel = User::query()->firstOrCreate(
            ['email' => 'daniel@thws.de'],
            [
                'name' => 'Daniel',
                'surname' => 'Goncharov',
                'password' => 'password',
            ]
        );
        $daniel->syncRoles([$superAdminRole, $studentRole, $professorRole]);

        $tim = User::query()->firstOrCreate(
            ['email' => 'tim@thws.de'],
            [
                'name' => 'Tim',
                'surname' => 'Admin',
                'password' => 'password',
            ]
        );
        $tim->syncRoles([$superAdminRole, $studentRole, $professorRole]);
    }
}
