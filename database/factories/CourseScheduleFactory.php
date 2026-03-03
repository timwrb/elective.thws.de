<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CourseSchedule>
 */
class CourseScheduleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'scheduled_at' => fake()->dateTimeBetween('now', '+12 weeks'),
            'duration_minutes' => fake()->randomElement([90, 120, 180, 240, 300]),
            'location' => null,
        ];
    }

    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_at' => fake()->dateTimeBetween('+1 week', '+12 weeks'),
        ]);
    }

    public function past(): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_at' => fake()->dateTimeBetween('-12 weeks', '-1 day'),
        ]);
    }

    public function morning(): static
    {
        return $this->state(function (array $attributes) {
            $date = fake()->dateTimeBetween('now', '+12 weeks');
            $date->setTime(fake()->numberBetween(8, 11), 0);

            return [
                'scheduled_at' => $date,
                'duration_minutes' => fake()->randomElement([90, 120, 180]),
            ];
        });
    }

    public function afternoon(): static
    {
        return $this->state(function (array $attributes) {
            $date = fake()->dateTimeBetween('now', '+12 weeks');
            $date->setTime(fake()->numberBetween(12, 15), 0);

            return [
                'scheduled_at' => $date,
                'duration_minutes' => fake()->randomElement([180, 240]),
            ];
        });
    }

    public function fullDay(): static
    {
        return $this->state(function (array $attributes) {
            $date = fake()->dateTimeBetween('now', '+12 weeks');
            $date->setTime(8, 0);

            return [
                'scheduled_at' => $date,
                'duration_minutes' => 480,
            ];
        });
    }

    public function withLocation(string $location): static
    {
        return $this->state(fn (array $attributes) => [
            'location' => $location,
        ]);
    }
}
