<?php

namespace App\Traits;

use App\Models\Semester;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasSemester
{
    /**
     * Get all semesters associated with this model.
     *
     * @return MorphToMany<Semester, $this>
     */
    public function semesters(): MorphToMany
    {
        return $this->morphToMany(
            Semester::class,
            'semesterable',
            'semesterables'
        )->withTimestamps();
    }

    /**
     * Scope query to only include models for a specific semester.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    protected function scopeForSemester(Builder $query, Semester $semester): Builder
    {
        return $query->whereHas('semesters', function (Builder $q) use ($semester): void {
            $q->where('semesters.id', $semester->id);
        });
    }

    /**
     * Check if this model is associated with a given semester.
     */
    public function isInSemester(Semester $semester): bool
    {
        return $this->semesters()->where('semesters.id', $semester->id)->exists();
    }

    /**
     * Associate this model with a semester.
     */
    public function assignToSemester(Semester $semester): void
    {
        if (! $this->isInSemester($semester)) {
            $this->semesters()->attach($semester);
        }
    }

    /**
     * Remove association with a semester.
     */
    public function removeFromSemester(Semester $semester): void
    {
        $this->semesters()->detach($semester);
    }

    /**
     * Sync semesters (replace all existing with new ones).
     *
     * @param  array<int>|Semester  $semesters
     */
    public function syncSemesters(array|Semester $semesters): void
    {
        if ($semesters instanceof Semester) {
            $semesters = [$semesters->id];
        }

        $this->semesters()->sync($semesters);
    }
}
