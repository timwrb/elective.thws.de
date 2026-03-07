<?php

namespace Database\Factories;

use App\Enums\ElectiveStatus;
use App\Enums\ExamType;
use App\Enums\Language;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Awpf>
 */
class AwpfFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'content' => fake()->paragraphs(3, true),
            'goals' => fake()->optional()->paragraph(),
            'literature' => fake()->optional()->sentence(),
            'credits' => fake()->randomElement([2.5, 5.0]),
            'max_participants' => fake()->optional()->numberBetween(20, 60),
            'hours_per_week' => fake()->optional()->randomElement([1.5, 2.0, 3.0]),
            'type_of_class' => fake()->optional()->randomElement(['Seminar', 'Vorlesung', 'Online', 'Blockseminar']),
            'language' => fake()->randomElement(Language::cases()),
            'exam_type' => fake()->randomElement(ExamType::cases()),
            'lecturer_name' => fake()->optional()->name(),
            'course_url' => fake()->url(),
        ];
    }

    public function withSchedules(int $count = 2): static
    {
        return $this->hasSchedules($count);
    }

    public function withProfessor(?User $user = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'professor_id' => $user?->id ?? User::factory(),
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ElectiveStatus::Published,
        ]);
    }
}
