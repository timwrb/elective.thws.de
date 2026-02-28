<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Semester>
 */
class SemesterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'year' => fake()->numberBetween(2020, 2030),
            'season' => fake()->randomElement(\App\Enums\Season::cases()),
        ];
    }

    /**
     * Create a winter semester.
     */
    public function winter(): static
    {
        return $this->state(fn (array $attributes) => [
            'season' => \App\Enums\Season::Winter,
        ]);
    }

    /**
     * Create a summer semester.
     */
    public function summer(): static
    {
        return $this->state(fn (array $attributes) => [
            'season' => \App\Enums\Season::Summer,
        ]);
    }

    /**
     * Create a semester for a specific year.
     */
    public function year(int $year): static
    {
        return $this->state(fn (array $attributes) => [
            'year' => $year,
        ]);
    }
}
