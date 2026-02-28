<?php

namespace Database\Factories;

use App\Enums\DayOfWeek;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CourseSchedule>
 */
class CourseScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'day_of_week' => fake()->randomElement(DayOfWeek::cases()),
            'start_time' => fake()->time('H:i:s', '18:00'),
            'duration_minutes' => fake()->randomElement([90, 120, 180, 240, 300]),
        ];
    }

    public function morning(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => fake()->time('H:i:s', '12:00'),
            'duration_minutes' => fake()->randomElement([90, 120, 180]),
        ]);
    }

    public function afternoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => fake()->numberBetween(12, 15).':00:00',
            'duration_minutes' => fake()->randomElement([180, 240]),
        ]);
    }

    public function weekday(): static
    {
        return $this->state(fn (array $attributes) => [
            'day_of_week' => fake()->randomElement([
                DayOfWeek::Monday,
                DayOfWeek::Tuesday,
                DayOfWeek::Wednesday,
                DayOfWeek::Thursday,
                DayOfWeek::Friday,
            ]),
        ]);
    }

    public function fullDay(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => '09:00:00',
            'duration_minutes' => 480,
        ]);
    }
}
