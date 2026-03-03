<?php

namespace App\Models;

use App\Enums\EnrollmentStatus;
use App\Enums\EnrollmentType;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property string $user_id
 * @property int $semester_id
 * @property string $elective_type
 * @property string $elective_choice_id
 * @property int|null $parent_elective_choice_id
 * @property EnrollmentStatus $status
 * @property EnrollmentType $enrollment_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class UserSelection extends Model
{
    protected function casts(): array
    {
        return [
            'status' => EnrollmentStatus::class,
            'enrollment_type' => EnrollmentType::class,
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Semester, $this> */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /** @return MorphTo<Model, $this> */
    public function elective(): MorphTo
    {
        return $this->morphTo('elective', 'elective_type', 'elective_choice_id');
    }

    /** @return BelongsTo<UserSelection, $this> */
    public function parentChoice(): BelongsTo
    {
        return $this->belongsTo(UserSelection::class, 'parent_elective_choice_id');
    }

    /** @return HasMany<UserSelection, $this> */
    public function childChoices(): HasMany
    {
        return $this->hasMany(UserSelection::class, 'parent_elective_choice_id');
    }

    /**
     * @param  Builder<UserSelection>  $query
     * @return Builder<UserSelection>
     */
    #[Scope]
    protected function forSemester(Builder $query, Semester $semester): Builder
    {
        return $query->where('semester_id', $semester->id);
    }

    /**
     * @param  Builder<UserSelection>  $query
     * @return Builder<UserSelection>
     */
    #[Scope]
    protected function forUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * @param  Builder<UserSelection>  $query
     * @return Builder<UserSelection>
     */
    #[Scope]
    protected function pending(Builder $query): Builder
    {
        return $query->where('status', EnrollmentStatus::Pending);
    }

    /**
     * @param  Builder<UserSelection>  $query
     * @return Builder<UserSelection>
     */
    #[Scope]
    protected function confirmed(Builder $query): Builder
    {
        return $query->where('status', EnrollmentStatus::Confirmed);
    }

    /**
     * @param  Builder<UserSelection>  $query
     * @return Builder<UserSelection>
     */
    #[Scope]
    protected function rejected(Builder $query): Builder
    {
        return $query->where('status', EnrollmentStatus::Rejected);
    }

    /**
     * @param  Builder<UserSelection>  $query
     * @return Builder<UserSelection>
     */
    #[Scope]
    protected function withdrawn(Builder $query): Builder
    {
        return $query->where('status', EnrollmentStatus::Withdrawn);
    }

    /**
     * @param  Builder<UserSelection>  $query
     * @return Builder<UserSelection>
     */
    #[Scope]
    protected function priorityBased(Builder $query): Builder
    {
        return $query->where('enrollment_type', EnrollmentType::Priority);
    }

    /**
     * @param  Builder<UserSelection>  $query
     * @return Builder<UserSelection>
     */
    #[Scope]
    protected function direct(Builder $query): Builder
    {
        return $query->where('enrollment_type', EnrollmentType::Direct);
    }

    /**
     * @param  Builder<UserSelection>  $query
     * @return Builder<UserSelection>
     */
    #[Scope]
    protected function researchProjects(Builder $query): Builder
    {
        return $query->where('elective_type', ResearchProject::class);
    }

    /**
     * @param  Builder<UserSelection>  $query
     * @return Builder<UserSelection>
     */
    #[Scope]
    protected function awpf(Builder $query): Builder
    {
        return $query->where('elective_type', Awpf::class);
    }

    /**
     * @param  Builder<UserSelection>  $query
     * @return Builder<UserSelection>
     */
    #[Scope]
    protected function fwpm(Builder $query): Builder
    {
        return $query->where('elective_type', Fwpm::class);
    }

    /**
     * @param  Builder<UserSelection>  $query
     * @return Builder<UserSelection>
     */
    #[Scope]
    protected function withElective(Builder $query): Builder
    {
        return $query->with('elective');
    }

    /**
     * @param  Builder<UserSelection>  $query
     * @return Builder<UserSelection>
     */
    #[Scope]
    protected function withUser(Builder $query): Builder
    {
        return $query->with('user');
    }

    /**
     * @param  Builder<UserSelection>  $query
     * @return Builder<UserSelection>
     */
    #[Scope]
    protected function withSemester(Builder $query): Builder
    {
        return $query->with('semester');
    }

    /**
     * @param  Builder<UserSelection>  $query
     * @return Builder<UserSelection>
     */
    #[Scope]
    protected function withAll(Builder $query): Builder
    {
        return $query->with(['user', 'semester', 'elective']);
    }

    // Helper methods

    public function isPending(): bool
    {
        return $this->status === EnrollmentStatus::Pending;
    }

    public function isConfirmed(): bool
    {
        return $this->status === EnrollmentStatus::Confirmed;
    }

    public function isRejected(): bool
    {
        return $this->status === EnrollmentStatus::Rejected;
    }

    public function isWithdrawn(): bool
    {
        return $this->status === EnrollmentStatus::Withdrawn;
    }

    public function isPriorityBased(): bool
    {
        return $this->enrollment_type === EnrollmentType::Priority;
    }

    public function isDirect(): bool
    {
        return $this->enrollment_type === EnrollmentType::Direct;
    }

    public function getPriorityOrder(): ?int
    {
        if (! $this->isPriorityBased()) {
            return null;
        }

        $count = 1;
        $current = $this->parentChoice;

        while ($current !== null) {
            $count++;
            $current = $current->parentChoice;
        }

        return $count;
    }
}
