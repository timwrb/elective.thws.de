<?php

namespace Database\Seeders;

use App\Enums\DegreeField;
use App\Enums\DegreeLevel;
use App\Models\StudyProgram;
use Illuminate\Database\Seeder;

class StudyProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Bachelor programs
        $bec = StudyProgram::query()->firstOrCreate(
            ['code' => 'BEC'],
            [
                'name_english' => 'Business Administration',
                'name_german' => __('Business Administration'),
                'degree_level' => DegreeLevel::Bachelor,
                'degree_field' => DegreeField::BusinessAdministration,
                'is_dual' => false,
                'active' => true,
            ]
        );

        $bin = StudyProgram::query()->firstOrCreate(
            ['code' => 'BIN'],
            [
                'name_english' => 'Business Informatics',
                'name_german' => __('Business Informatics'),
                'degree_level' => DegreeLevel::Bachelor,
                'degree_field' => DegreeField::Science,
                'is_dual' => false,
                'active' => true,
            ]
        );

        $bwi = StudyProgram::query()->firstOrCreate(
            ['code' => 'BWI'],
            [
                'name_english' => 'Industrial Engineering',
                'name_german' => __('Industrial Engineering'),
                'degree_level' => DegreeLevel::Bachelor,
                'degree_field' => DegreeField::Engineering,
                'is_dual' => false,
                'active' => true,
            ]
        );

        $bdg = StudyProgram::query()->firstOrCreate(
            ['code' => 'BDG'],
            [
                'name_english' => 'Distributed and Green Computing',
                'name_german' => __('Distributed and Green Computing'),
                'degree_level' => DegreeLevel::Bachelor,
                'degree_field' => DegreeField::Science,
                'is_dual' => false,
                'active' => true,
            ]
        );

        // Dual variant of BDG
        StudyProgram::query()->firstOrCreate(
            ['code' => 'BDGD'],
            [
                'name_english' => 'Distributed and Green Computing (Dual)',
                'name_german' => __('Distributed and Green Computing').' (Dual)',
                'degree_level' => DegreeLevel::Bachelor,
                'degree_field' => DegreeField::Science,
                'is_dual' => true,
                'base_program_id' => $bdg->id,
                'active' => true,
            ]
        );

        $bis = StudyProgram::query()->firstOrCreate(
            ['code' => 'BIS'],
            [
                'name_english' => 'Business IT Solutions',
                'name_german' => __('Business IT Solutions'),
                'degree_level' => DegreeLevel::Bachelor,
                'degree_field' => DegreeField::Science,
                'is_dual' => false,
                'active' => true,
            ]
        );

        // Dual variant of BIS
        StudyProgram::query()->firstOrCreate(
            ['code' => 'BISD'],
            [
                'name_english' => 'Business IT Solutions (Dual)',
                'name_german' => __('Business IT Solutions').' (Dual)',
                'degree_level' => DegreeLevel::Bachelor,
                'degree_field' => DegreeField::Science,
                'is_dual' => true,
                'base_program_id' => $bis->id,
                'active' => true,
            ]
        );
    }
}
