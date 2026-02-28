<?php

namespace App\Models;

use App\Enums\ElectiveStatus;
use App\Enums\ExamType;
use App\Enums\Language;
use App\Traits\HasOrderedUserChoices;
use App\Traits\HasSemester;
use Database\Factories\AwpfFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $name
 * @property string|null $content
 * @property int $credits
 * @property Language $language
 * @property ExamType $exam_type
 * @property ElectiveStatus $status
 * @property string|null $professor_id
 * @property string|null $course_url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $formatted_schedules
 */
class Awpf extends Model
{
    /** @use HasFactory<AwpfFactory> */
    use HasFactory;

    use HasOrderedUserChoices;
    use HasSemester;
    use HasUuids;

    protected function casts(): array
    {
        return [
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

    /** @return Attribute<string, never> */
    protected function formattedSchedules(): Attribute
    {
        return Attribute::make(get: fn () => $this->schedules()
            ->orderedByDay()
            ->get()
            ->pluck('formatted_schedule')
            ->join(', '));
    }
}
