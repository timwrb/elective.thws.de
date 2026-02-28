<?php

namespace Database\Seeders;

use App\Enums\DayOfWeek;
use App\Models\Awpf;
use Illuminate\Database\Seeder;

class AwpfSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Awpf::factory()
            ->count(10)
            ->hasSchedules(2, function () {
                return [
                    'day_of_week' => fake()->randomElement([
                        DayOfWeek::Monday,
                        DayOfWeek::Tuesday,
                        DayOfWeek::Wednesday,
                        DayOfWeek::Thursday,
                        DayOfWeek::Friday,
                    ]),
                    'start_time' => fake()->numberBetween(8, 16).':00:00',
                    'duration_minutes' => fake()->randomElement([90, 120, 180]),
                ];
            })
            ->create();

        Awpf::factory()->create([
            'name' => 'Advanced Web Development',
            'course_url' => 'https://www.thws.de/courses/advanced-web-development',
        ])->schedules()->createMany([
            [
                'day_of_week' => DayOfWeek::Monday,
                'start_time' => '10:00:00',
                'duration_minutes' => 300,
            ],
            [
                'day_of_week' => DayOfWeek::Wednesday,
                'start_time' => '10:00:00',
                'duration_minutes' => 300,
            ],
            [
                'day_of_week' => DayOfWeek::Friday,
                'start_time' => '09:00:00',
                'duration_minutes' => 540,
            ],
        ]);
    }
}
