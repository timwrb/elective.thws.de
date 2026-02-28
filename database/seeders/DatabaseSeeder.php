<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SemesterSeeder::class,
            StudyProgramSeeder::class,
            FwpmSeeder::class,
        ]);

        Role::firstOrCreate(['name' => 'student']);

        User::factory()->create([
            'email' => 'test@student.de',
            'password' => '12345678',
        ])->assignRole('student');

        User::factory()->create([
            'email' => 'test@admin.de',
            'password' => '12345678',
        ]);
    }
}
