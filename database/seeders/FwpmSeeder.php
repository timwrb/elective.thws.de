<?php

namespace Database\Seeders;

use App\Enums\ElectiveStatus;
use App\Enums\ExamType;
use App\Enums\Language;
use App\Enums\Season;
use App\Models\Fwpm;
use App\Models\Semester;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class FwpmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = base_path('example_fwpm.json');

        if (! file_exists($jsonPath)) {
            $this->command->warn('example_fwpm.json not found. Skipping FWPM seeding.');

            return;
        }

        $courses = json_decode(file_get_contents($jsonPath), true);

        if (! is_array($courses)) {
            $this->command->error('Invalid JSON format in example_fwpm.json');

            return;
        }

        $this->command->info('Seeding '.count($courses).' FWPM courses from JSON...');

        foreach ($courses as $index => $courseData) {
            try {
                // Parse semester (e.g., "2025ws" -> year: 2025, season: WS)
                $semesterStr = $courseData['semester'] ?? null;

                if (! $semesterStr) {
                    $this->command->warn("Skipping course {$index}: No semester specified");

                    continue;
                }

                $year = (int) substr($semesterStr, 0, 4);
                $seasonStr = strtoupper(substr($semesterStr, 4)); // "ws" -> "WS"
                $season = $seasonStr === 'WS' ? Season::Winter : Season::Summer;

                $semester = Semester::query()->firstOrCreate([
                    'year' => $year,
                    'season' => $season,
                ]);

                // Map German exam type to English enum
                $examType = ExamType::fromGermanType($courseData['typeOfExam'] ?? 'Schriftliche Prüfung');

                // Determine language
                $language = ($courseData['language'] ?? 'Deutsch') === 'Deutsch'
                    ? Language::German
                    : Language::English;

                // Create or find professor user
                $professorId = null;
                $lecturerNames = $courseData['lecturerFullNames'] ?? null;

                if ($lecturerNames) {
                    // Take the first professor if multiple are listed
                    $firstLecturer = explode(',', $lecturerNames)[0];
                    $professor = $this->createOrFindProfessor(trim($firstLecturer));

                    if ($professor) {
                        $professorId = $professor->id;
                    }
                }

                // Create FWPM
                $fwpm = Fwpm::query()->create([
                    'fiwis_id' => $courseData['id'],
                    'module_number' => $courseData['moduleNumbers'] ?? null,
                    'name_german' => $courseData['nameGerman'] ?? 'Unbekannt',
                    'name_english' => $courseData['nameEnglish'] ?? 'Unknown',
                    'contents' => $courseData['contents'] ?? null,
                    'credits' => (int) ($courseData['creditPoints'] ?? 5),
                    'max_participants' => $courseData['maxParticipants'] ?? null,
                    'hours_per_week' => $courseData['hoursLecturesPerWeek'] ?? null,
                    'type_of_class' => $courseData['typeOfClass'] ?? null,
                    'recommended_semester' => $courseData['lpSemester'] ?? null,
                    'goals' => $courseData['goals'] ?? null,
                    'literature' => $courseData['literature'] ?? null,
                    'media' => $courseData['media'] ?? null,
                    'tools' => $courseData['tools'] ?? null,
                    'prerequisite_recommended' => $courseData['prerequisiteRecommended'] ?? null,
                    'prerequisite_formal' => $courseData['prerequisiteAccordingToSer'] ?? null,
                    'total_hours_lectures' => $courseData['totalHoursLectures'] ?? null,
                    'total_hours_self_study' => $courseData['totalHoursSelfStudy'] ?? null,
                    'language' => $language,
                    'exam_type' => $examType,
                    'status' => ElectiveStatus::Published,
                    'professor_id' => $professorId,
                    'lecturer_name' => $courseData['lecturerFullNames'] ?? null,
                    'semester_id' => $semester->id,
                ]);

                // Attach study programs with approval status
                $programsStr = $courseData['studyProgramsFinal'] ?? '';

                if (! empty($programsStr)) {
                    $programCodes = explode('#', $programsStr);

                    foreach ($programCodes as $code) {
                        $code = trim($code);

                        $program = StudyProgram::query()->where('code', $code)->first();

                        if ($program) {
                            // Get approval status for this program
                            $approvalKey = strtolower($code).'Approved';
                            $approvalStatus = $courseData[$approvalKey] ?? 0;

                            $fwpm->studyPrograms()->attach($program->id, [
                                'approval_status' => $approvalStatus,
                            ]);
                        } else {
                            $this->command->warn("Study program '{$code}' not found for course {$fwpm->name_english}");
                        }
                    }
                }

                $this->command->info("✓ Seeded: {$fwpm->name_english}");
            } catch (\Exception $e) {
                $this->command->error("Failed to seed course {$index}: ".$e->getMessage());
            }
        }

        $this->command->info('FWPM seeding completed!');
    }

    /**
     * Create or find a professor user from a name string.
     */
    protected function createOrFindProfessor(string $fullName): ?User
    {
        // Remove titles like "Prof. Dr.", "Prof. Dr.-Ing.", etc.
        $cleanName = preg_replace('/^(Prof\.|Dr\.|Dr\.-Ing\.|M\.Sc\.|B\.Sc\.)\s*/i', '', $fullName);
        $cleanName = trim($cleanName);

        if (empty($cleanName)) {
            return null;
        }

        // Split name into parts
        $nameParts = explode(' ', $cleanName);

        if (count($nameParts) < 2) {
            // Can't determine first and last name
            return null;
        }

        // Assume last part is surname, rest is name
        $surname = array_pop($nameParts);
        $name = implode(' ', $nameParts);

        // Generate email from name (lowercase, no spaces)
        $email = Str::slug($name.'.'.$surname, '.').'@thws.de';

        // Try to find existing user by email
        $user = User::query()->where('email', $email)->first();

        if ($user) {
            // Ensure they have professor role
            if (! $user->hasRole('professor')) {
                $user->assignRole('professor');
            }

            return $user;
        }

        // Create new professor
        $user = User::query()->create([
            'name' => $name,
            'surname' => $surname,
            'email' => $email,
            'password' => bcrypt('password'), // Default password
        ]);

        // Ensure professor role exists
        $professorRole = Role::query()->firstOrCreate(['name' => 'professor']);

        // Assign professor role
        $user->assignRole($professorRole);

        $this->command->info("  → Created professor: {$name} {$surname} ({$email})");

        return $user;
    }
}
