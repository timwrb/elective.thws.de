<?php

namespace Database\Seeders;

use App\Enums\Season;
use App\Models\Semester;
use Illuminate\Database\Seeder;

class SemesterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $semesters = [
            ['year' => 2023, 'season' => Season::Winter],
            ['year' => 2024, 'season' => Season::Summer],
            ['year' => 2024, 'season' => Season::Winter],
            ['year' => 2025, 'season' => Season::Summer],
            ['year' => 2025, 'season' => Season::Winter],
            ['year' => 2026, 'season' => Season::Summer],
            ['year' => 2026, 'season' => Season::Winter],
        ];

        foreach ($semesters as $semester) {
            Semester::query()->firstOrCreate($semester);
        }
    }
}
