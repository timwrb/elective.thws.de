<?php

namespace Database\Factories;

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
            'credits' => fake()->randomElement([5, 10, 15]),
            'language' => fake()->randomElement(Language::cases()),
            'exam_type' => fake()->randomElement(ExamType::cases()),
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
}
