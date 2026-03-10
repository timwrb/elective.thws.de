<?php

namespace App\Models;

use Database\Factories\CourseScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $schedulable_type
 * @property string $schedulable_id
 * @property Carbon $scheduled_at
 * @property int $duration_minutes
 * @property string|null $location
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $formatted_schedule
 */
class CourseSchedule extends Model
{
    /** @use HasFactory<CourseScheduleFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'duration_minutes' => 'integer',
        ];
    }

    /** @return MorphTo<Model, $this> */
    public function schedulable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return Attribute<string, never> */
    protected function formattedSchedule(): Attribute
    {
        return Attribute::make(get: function () {
            $base = sprintf(
                '%s, %s (%d min.)',
                $this->scheduled_at->format('d.m.Y'),
                $this->scheduled_at->format('H:i'),
                $this->duration_minutes
            );

            return filled($this->location)
                ? $base.' — '.$this->location
                : $base;
        });
    }

    /**
     * @param  Builder<CourseSchedule>  $query
     * @return Builder<CourseSchedule>
     */
    #[Scope]
    protected function orderedByDate(Builder $query): Builder
    {
        return $query->orderBy('scheduled_at');
    }

    /**
     * @param  Builder<CourseSchedule>  $query
     * @return Builder<CourseSchedule>
     */
    #[Scope]
    protected function upcoming(Builder $query): Builder
    {
        return $query->where('scheduled_at', '>=', now());
    }
}
