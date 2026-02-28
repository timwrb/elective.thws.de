<?php

namespace App\Models;

use App\Enums\ElectiveStatus;
use App\Traits\HasOrderedUserChoices;
use App\Traits\HasSemester;
use Database\Factories\ResearchProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property string $id
 * @property string $title
 * @property string|null $description
 * @property string|null $professor_id
 * @property string|null $creator_id
 * @property int|null $semester_id
 * @property int $credits
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property int $max_students
 * @property ElectiveStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ResearchProject extends Model implements HasMedia
{
    /** @use HasFactory<ResearchProjectFactory> */
    use HasFactory;

    use HasOrderedUserChoices;
    use HasSemester;
    use HasUuids;
    use InteractsWithMedia;

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => ElectiveStatus::class,
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function professor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'professor_id');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /** @return BelongsTo<Semester, $this> */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /** @return HasMany<UserSelection, $this> */
    public function enrollments(): HasMany
    {
        return $this->hasMany(UserSelection::class, 'elective_choice_id')
            ->where('elective_type', self::class);
    }

    /**
     * @param  Builder<ResearchProject>  $query
     * @return Builder<ResearchProject>
     */
    #[Scope]
    protected function createdByStudent(Builder $query): Builder
    {
        return $query->whereNotNull('creator_id');
    }

    /**
     * @param  Builder<ResearchProject>  $query
     * @return Builder<ResearchProject>
     */
    #[Scope]
    protected function createdByAdmin(Builder $query): Builder
    {
        return $query->whereNull('creator_id');
    }

    /**
     * @param  Builder<ResearchProject>  $query
     * @return Builder<ResearchProject>
     */
    #[Scope]
    protected function withAvailableCapacity(Builder $query, Semester $semester): Builder
    {
        return $query->whereHas('enrollments', function (Builder $q) use ($semester): void {
            $q->where('semester_id', $semester->id)
                ->whereIn('status', ['pending', 'confirmed']);
        }, '<', DB::raw('max_students'));
    }

    public function getCurrentEnrollmentCount(Semester $semester): int
    {
        return $this->enrollments()
            ->forSemester($semester)
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();
    }

    public function getAvailableSpots(Semester $semester): int
    {
        return max(0, $this->max_students - $this->getCurrentEnrollmentCount($semester));
    }

    public function hasCapacity(Semester $semester): bool
    {
        return $this->getAvailableSpots($semester) > 0;
    }

    public function isFull(Semester $semester): bool
    {
        return ! $this->hasCapacity($semester);
    }

    public function isUserEnrolled(User $user, Semester $semester): bool
    {
        return $this->enrollments()
            ->forUser($user)
            ->forSemester($semester)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();
    }

    public function isCreatedBy(User $user): bool
    {
        return $this->creator_id === $user->id;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments')
            ->useDisk('public');
    }
}
