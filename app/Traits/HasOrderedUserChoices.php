<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Semester;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;

trait HasOrderedUserChoices
{
    /**
     * Get users who have selected this elective, with their ordered choices.
     *
     * @return MorphToMany<User, $this>
     */
    public function orderedUserChoices(): MorphToMany
    {
        return $this->morphedByMany(
            User::class,
            'elective',
            'user_selections',
            'elective_choice_id',
            'user_id'
        )
            ->withPivot(['semester_id', 'parent_elective_choice_id', 'id'])
            ->withTimestamps();
    }

    /**
     * Get ordered choices for a specific user and semester.
     *
     * @return Collection<int, User>
     */
    public function getChoicesForUserAndSemester(User $user, Semester $semester): Collection
    {
        return $this->orderedUserChoices()
            ->wherePivot('user_id', $user->id)
            ->wherePivot('semester_id', $semester->id)
            ->get();
    }

    /**
     * Get all users with their ordered choices for a specific semester.
     *
     * @return Collection<int, User>
     */
    public function getUsersForSemester(Semester $semester): Collection
    {
        return $this->orderedUserChoices()
            ->wherePivot('semester_id', $semester->id)
            ->get();
    }
}
