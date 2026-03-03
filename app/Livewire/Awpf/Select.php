<?php

namespace App\Livewire\Awpf;

use App\Enums\ElectiveStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\EnrollmentType;
use App\Models\Awpf;
use App\Models\Semester;
use App\Models\User;
use App\Models\UserSelection;
use App\Services\SemesterService;
use App\Settings\AwpfSettings;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('My AWPF Selection')]
class Select extends Component
{
    /** @var list<string> Ordered list of Awpf UUIDs representing the student's ranked choices */
    public array $rankedIds = [];

    public bool $showConfirmation = false;

    public function mount(): void
    {
        $this->loadExistingSelections();
    }

    private function loadExistingSelections(): void
    {
        $semester = app(SemesterService::class)->getCurrentSemester();

        if (! $semester instanceof Semester) {
            return;
        }

        $user = auth()->user();

        if (! $user instanceof User) {
            return;
        }

        $selections = UserSelection::query()
            ->forUser($user)
            ->forSemester($semester)
            ->awpf()
            ->priorityBased()
            ->get();

        $this->rankedIds = $this->buildOrderedIds($selections);
    }

    /**
     * Reconstruct ordered UUID array from the singly-linked-list structure.
     *
     * Each UserSelection points to its predecessor via parent_elective_choice_id.
     * The root (first choice) has parent_elective_choice_id = null.
     *
     * @param  Collection<int, UserSelection>  $selections
     * @return list<string>
     */
    private function buildOrderedIds(Collection $selections): array
    {
        if ($selections->isEmpty()) {
            return [];
        }

        $first = $selections->firstWhere('parent_elective_choice_id', null);

        if (! $first instanceof UserSelection) {
            return [];
        }

        $ordered = [];
        $current = $first;

        while ($current instanceof UserSelection) {
            $ordered[] = $current->elective_choice_id;
            $current = $selections->firstWhere('parent_elective_choice_id', $current->id);
        }

        return $ordered;
    }

    #[Computed]
    public function currentSemester(): ?Semester
    {
        return app(SemesterService::class)->getCurrentSemester();
    }

    #[Computed]
    public function settings(): AwpfSettings
    {
        return app(AwpfSettings::class);
    }

    /** @return Collection<int, Awpf> */
    #[Computed]
    public function availableCourses(): Collection
    {
        $semester = $this->currentSemester;

        return Awpf::query()
            ->where('status', ElectiveStatus::Published)
            ->when($semester instanceof Semester, fn ($q) => $q->forSemester($semester))
            ->whereNotIn('id', $this->rankedIds)
            ->with(['professor', 'schedules'])
            ->get();
    }

    /** @return Collection<int, Awpf> */
    #[Computed]
    public function rankedCourses(): Collection
    {
        if (empty($this->rankedIds)) {
            return collect();
        }

        $courses = Awpf::query()
            ->whereIn('id', $this->rankedIds)
            ->with(['professor', 'schedules'])
            ->get()
            ->keyBy('id');

        return collect($this->rankedIds)
            ->map(fn (string $id): ?Awpf => $courses->get($id))
            ->filter()
            ->values();
    }

    public function addCourse(string $awpfId): void
    {
        if (! $this->settings->enrollmentOpen) {
            return;
        }

        if (in_array($awpfId, $this->rankedIds, true)) {
            return;
        }

        if (count($this->rankedIds) >= $this->settings->maxSelections) {
            $this->addError('rankedIds', __('You may select at most :max courses.', ['max' => $this->settings->maxSelections]));

            return;
        }

        $this->rankedIds[] = $awpfId;

        unset($this->availableCourses, $this->rankedCourses);
    }

    public function removeCourse(string $awpfId): void
    {
        if (! $this->settings->enrollmentOpen) {
            return;
        }

        $this->rankedIds = array_values(
            array_filter($this->rankedIds, fn (string $id): bool => $id !== $awpfId)
        );

        unset($this->availableCourses, $this->rankedCourses);
    }

    public function moveUp(int $index): void
    {
        if ($index <= 0 || $index >= count($this->rankedIds)) {
            return;
        }

        [$this->rankedIds[$index - 1], $this->rankedIds[$index]] =
            [$this->rankedIds[$index], $this->rankedIds[$index - 1]];

        unset($this->rankedCourses);
    }

    public function moveDown(int $index): void
    {
        if ($index < 0 || $index >= count($this->rankedIds) - 1) {
            return;
        }

        [$this->rankedIds[$index], $this->rankedIds[$index + 1]] =
            [$this->rankedIds[$index + 1], $this->rankedIds[$index]];

        unset($this->rankedCourses);
    }

    public function confirmSave(): void
    {
        if (! $this->settings->enrollmentOpen) {
            return;
        }

        if (count($this->rankedIds) < $this->settings->minRequiredSelections) {
            $this->addError('rankedIds', __('Please select at least :min courses.', ['min' => $this->settings->minRequiredSelections]));

            return;
        }

        $this->showConfirmation = true;
    }

    public function saveSelections(): void
    {
        if (! $this->settings->enrollmentOpen) {
            return;
        }

        $semester = $this->currentSemester;

        if (! $semester instanceof Semester) {
            return;
        }

        $user = auth()->user();

        if (! $user instanceof User) {
            return;
        }

        UserSelection::query()
            ->forUser($user)
            ->forSemester($semester)
            ->awpf()
            ->delete();

        $previousId = null;

        foreach ($this->rankedIds as $awpfId) {
            $selection = UserSelection::query()->create([
                'user_id' => $user->id,
                'semester_id' => $semester->id,
                'elective_type' => Awpf::class,
                'elective_choice_id' => $awpfId,
                'parent_elective_choice_id' => $previousId,
                'status' => EnrollmentStatus::Pending,
                'enrollment_type' => EnrollmentType::Priority,
            ]);

            $previousId = $selection->id;
        }

        $this->showConfirmation = false;
        session()->flash('status', __('Your selections have been saved.'));
    }

    public function render(): View
    {
        return view('livewire.awpf.select');
    }
}
