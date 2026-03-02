<?php

namespace App\Services;

use App\Enums\ElectiveStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\EnrollmentType;
use App\Models\Fwpm;
use App\Models\Semester;
use App\Models\User;
use App\Models\UserSelection;
use Illuminate\Support\Collection;

class FwpmSelectionService
{
    /**
     * @return array{min: int, max: int}
     */
    public function getChoiceBounds(int $desiredCount): array
    {
        return [
            'min' => $desiredCount,
            'max' => $desiredCount + 2,
        ];
    }

    /**
     * @return array{min: int, max: int}
     */
    public function getDesiredCountRange(): array
    {
        return [
            'min' => 1,
            'max' => 5,
        ];
    }

    /**
     * @param  array<int, string>  $orderedFwpmIds
     */
    public function saveSelections(User $user, Semester $semester, array $orderedFwpmIds, int $desiredCount): void
    {
        $publishedIds = Fwpm::query()
            ->where('semester_id', $semester->id)
            ->where('status', ElectiveStatus::Published)
            ->whereIn('id', $orderedFwpmIds)
            ->pluck('id')
            ->all();

        if (count($publishedIds) !== count($orderedFwpmIds) || count($orderedFwpmIds) !== count(array_unique($orderedFwpmIds))) {
            throw new \InvalidArgumentException('Invalid FWPM IDs provided.');
        }

        UserSelection::query()
            ->forUser($user)
            ->forSemester($semester)
            ->fwpm()
            ->delete();

        $parentId = null;

        foreach ($orderedFwpmIds as $fwpmId) {
            $selection = UserSelection::create([
                'user_id' => $user->id,
                'semester_id' => $semester->id,
                'elective_type' => Fwpm::class,
                'elective_choice_id' => $fwpmId,
                'parent_elective_choice_id' => $parentId,
                'status' => EnrollmentStatus::Pending,
                'enrollment_type' => EnrollmentType::Priority,
            ]);

            $parentId = $selection->id;
        }
    }

    /**
     * @return array<int, string>
     */
    public function loadSelections(User $user, Semester $semester): array
    {
        $selections = UserSelection::query()
            ->forUser($user)
            ->forSemester($semester)
            ->fwpm()
            ->get();

        if ($selections->isEmpty()) {
            return [];
        }

        $root = $selections->firstWhere('parent_elective_choice_id', null);

        if (! $root) {
            return [];
        }

        $indexed = $selections->keyBy('parent_elective_choice_id');
        $ordered = [];
        $current = $root;

        while ($current) {
            $ordered[] = $current->elective_choice_id;
            $current = $indexed->get($current->id);
        }

        return $ordered;
    }

    /**
     * @return Collection<int, UserSelection>
     */
    public function loadSelectionsWithStatus(User $user, Semester $semester): Collection
    {
        $selections = UserSelection::query()
            ->forUser($user)
            ->forSemester($semester)
            ->fwpm()
            ->withElective()
            ->get();

        if ($selections->isEmpty()) {
            return collect();
        }

        $root = $selections->firstWhere('parent_elective_choice_id', null);

        if (! $root) {
            return collect();
        }

        $indexed = $selections->keyBy('parent_elective_choice_id');
        $ordered = collect();
        $current = $root;

        while ($current) {
            $ordered->push($current);
            $current = $indexed->get($current->id);
        }

        return $ordered;
    }
}
