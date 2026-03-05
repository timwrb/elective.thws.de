<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserSelection>
 */
class UserSelectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'semester_id' => \App\Models\Semester::factory(),
            'elective_type' => \App\Models\ResearchProject::class,
            'elective_choice_id' => \App\Models\ResearchProject::factory(),
            'enrollment_type' => \App\Enums\EnrollmentType::Direct,
            'status' => \App\Enums\EnrollmentStatus::Pending,
        ];
    }
}
