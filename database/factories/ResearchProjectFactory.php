<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ResearchProject>
 */
class ResearchProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'credits' => fake()->numberBetween(5, 10),
            'max_students' => fake()->numberBetween(1, 5),
            'start_date' => null,
            'end_date' => null,
        ];
    }

    public function withProfessor(?User $user = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'professor_id' => $user?->id ?? User::factory(),
        ]);
    }
}
