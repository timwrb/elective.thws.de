<?php

namespace App\Models;

use App\Enums\ElectiveStatus;
use App\Enums\ExamType;
use App\Enums\Language;
use App\Traits\HasOrderedUserChoices;
use App\Traits\HasSemester;
use Database\Factories\FwpmFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int $fiwis_id
 * @property string|null $module_number
 * @property string $name_german
 * @property string $name_english
 * @property string|null $contents
 * @property int $credits
 * @property int|null $max_participants
 * @property float|null $hours_per_week
 * @property string|null $type_of_class
 * @property string|null $recommended_semester
 * @property string|null $goals
 * @property string|null $literature
 * @property string|null $media
 * @property string|null $tools
 * @property string|null $prerequisite_recommended
 * @property string|null $prerequisite_formal
 * @property int|null $total_hours_lectures
 * @property int|null $total_hours_self_study
 * @property Language $language
 * @property ExamType $exam_type
 * @property ElectiveStatus $status
 * @property string|null $professor_id
 * @property string|null $lecturer_name
 * @property int|null $semester_id
 * @property string|null $course_url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $formatted_schedules
 */
class Fwpm extends Model
{
    /** @use HasFactory<FwpmFactory> */
    use HasFactory;

    use HasOrderedUserChoices;
    use HasSemester;
    use HasUuids;

    protected function casts(): array
    {
        return [
            'hours_per_week' => 'decimal:1',
            'language' => Language::class,
            'exam_type' => ExamType::class,
            'status' => ElectiveStatus::class,
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function professor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'professor_id');
    }

    /** @return MorphMany<CourseSchedule, $this> */
    public function schedules(): MorphMany
    {
        return $this->morphMany(CourseSchedule::class, 'schedulable');
    }

    /** @return BelongsTo<Semester, $this> */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /** @return BelongsToMany<StudyProgram, $this> */
    public function studyPrograms(): BelongsToMany
    {
        return $this->belongsToMany(StudyProgram::class, 'fwpm_study_program')
            ->withPivot('approval_status')
            ->withTimestamps();
    }

    /** @return Attribute<string, never> */
    protected function formattedSchedules(): Attribute
    {
        return Attribute::make(get: fn () => $this->schedules()
            ->orderedByDay()
            ->get()
            ->pluck('formatted_schedule')
            ->join(', '));
    }

    /** @return Attribute<string, never> */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => app()->getLocale() === 'de'
                ? $this->name_german
                : $this->name_english
        );
    }
}
