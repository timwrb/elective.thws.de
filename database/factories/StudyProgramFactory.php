<?php

namespace Database\Factories;

use App\Enums\DegreeField;
use App\Enums\DegreeLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudyProgram>
 */
class StudyProgramFactory extends Factory
{
    /**
     * Pool of real THWS study programs
     *
     * @var array<int, array<string, mixed>>
     */
    protected static array $programs = [
        ['code' => 'BEC', 'name' => 'Business Administration', 'level' => DegreeLevel::Bachelor, 'field' => DegreeField::BusinessAdministration],
        ['code' => 'BIN', 'name' => 'Business Informatics', 'level' => DegreeLevel::Bachelor, 'field' => DegreeField::Science],
        ['code' => 'BWI', 'name' => 'Industrial Engineering', 'level' => DegreeLevel::Bachelor, 'field' => DegreeField::Engineering],
        ['code' => 'BDG', 'name' => 'Distributed and Green Computing', 'level' => DegreeLevel::Bachelor, 'field' => DegreeField::Science],
        ['code' => 'BIS', 'name' => 'Business IT Solutions', 'level' => DegreeLevel::Bachelor, 'field' => DegreeField::Science],
        ['code' => 'INF', 'name' => 'Computer Science', 'level' => DegreeLevel::Bachelor, 'field' => DegreeField::Science],
        ['code' => 'SWE', 'name' => 'Software Engineering', 'level' => DegreeLevel::Bachelor, 'field' => DegreeField::Science],
        ['code' => 'EIT', 'name' => 'Electrical Engineering and Information Technology', 'level' => DegreeLevel::Bachelor, 'field' => DegreeField::Engineering],
        ['code' => 'MBA', 'name' => 'Mechanical Engineering', 'level' => DegreeLevel::Bachelor, 'field' => DegreeField::Engineering],
        ['code' => 'MEC', 'name' => 'Mechatronics', 'level' => DegreeLevel::Bachelor, 'field' => DegreeField::Engineering],
        ['code' => 'ROB', 'name' => 'Robotics', 'level' => DegreeLevel::Bachelor, 'field' => DegreeField::Engineering],
        ['code' => 'LOG', 'name' => 'Logistics', 'level' => DegreeLevel::Bachelor, 'field' => DegreeField::BusinessAdministration],
        ['code' => 'ECO', 'name' => 'E-Commerce', 'level' => DegreeLevel::Bachelor, 'field' => DegreeField::BusinessAdministration],
        ['code' => 'BAN', 'name' => 'Business Analytics', 'level' => DegreeLevel::Bachelor, 'field' => DegreeField::BusinessAdministration],
        ['code' => 'ARC', 'name' => 'Architecture', 'level' => DegreeLevel::Bachelor, 'field' => DegreeField::Engineering],
        ['code' => 'MAI', 'name' => 'Artificial Intelligence', 'level' => DegreeLevel::Master, 'field' => DegreeField::Science],
        ['code' => 'MEC', 'name' => 'Business Administration', 'level' => DegreeLevel::Master, 'field' => DegreeField::BusinessAdministration],
        ['code' => 'MDB', 'name' => 'Digital Business Systems', 'level' => DegreeLevel::Master, 'field' => DegreeField::BusinessAdministration],
        ['code' => 'MIS', 'name' => 'International Management', 'level' => DegreeLevel::Master, 'field' => DegreeField::BusinessAdministration],
    ];

    protected static int $programIndex = 0;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Get the next program from the pool
        $program = self::$programs[self::$programIndex % count(self::$programs)];
        self::$programIndex++;

        return [
            'code' => $program['code'],
            'name_english' => $program['name'],
            'name_german' => __($program['name']),
            'degree_level' => $program['level'],
            'degree_field' => $program['field'],
            'is_dual' => false,
            'base_program_id' => null,
            'active' => true,
        ];
    }

    public function dual(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_dual' => true,
            'code' => $attributes['code'].'D',
        ]);
    }
}
