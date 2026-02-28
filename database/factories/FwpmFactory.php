<?php

namespace Database\Factories;

use App\Enums\ExamType;
use App\Enums\Language;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Fwpm>
 */
class FwpmFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'fiwis_id' => fake()->unique()->numberBetween(100000, 999999),
            'module_number' => fake()->numerify('5003###'),
            'name_german' => fake()->words(3, true),
            'name_english' => fake()->words(3, true),
            'contents' => fake()->paragraphs(3, true),
            'credits' => fake()->randomElement([5, 10, 15]),
            'max_participants' => fake()->numberBetween(15, 30),
            'hours_per_week' => fake()->randomElement([2.0, 4.0, 6.0]),
            'type_of_class' => fake()->randomElement(['Seminar', 'Vorlesung', 'Praktikum']),
            'recommended_semester' => (string) fake()->numberBetween(5, 7),
            'goals' => fake()->paragraphs(2, true),
            'literature' => fake()->sentence(),
            'media' => fake()->sentence(),
            'tools' => fake()->sentence(),
            'prerequisite_recommended' => fake()->sentence(),
            'prerequisite_formal' => 'keine',
            'total_hours_lectures' => fake()->numberBetween(30, 90),
            'total_hours_self_study' => fake()->numberBetween(60, 120),
            'language' => fake()->randomElement(Language::cases()),
            'exam_type' => fake()->randomElement(ExamType::cases()),
            'lecturer_name' => fake()->name(),
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
