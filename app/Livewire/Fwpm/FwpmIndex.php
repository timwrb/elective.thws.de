<?php

namespace App\Livewire\Fwpm;

use App\Enums\ElectiveStatus;
use App\Models\Fwpm;
use App\Models\Semester;
use App\Services\FwpmSelectionService;
use App\Services\SelectionPeriodService;
use App\Services\SemesterService;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app')]
class FwpmIndex extends Component
{
    public Semester $semester;

    public string $search = '';

    public function mount(Semester $semester): void
    {
        $this->semester = $semester;
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, Fwpm> */
    #[Computed]
    public function availableFwpms(): \Illuminate\Database\Eloquent\Collection
    {
        return Fwpm::query()
            ->where('semester_id', $this->semester->id)
            ->where('status', ElectiveStatus::Published)
            ->with(['professor', 'schedules', 'studyPrograms'])
            ->when($this->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name_german', 'like', "%{$search}%")
                        ->orWhere('name_english', 'like', "%{$search}%");
                });
            })
            ->orderBy('name_german')
            ->get();
    }

    #[Computed]
    public function isSelectionOpen(): bool
    {
        return app(SelectionPeriodService::class)->isSelectionOpen($this->semester);
    }

    #[Computed]
    public function isPastSemester(): bool
    {
        return app(SemesterService::class)->isPastSemester($this->semester);
    }

    /** @return array<int, string> */
    #[Computed]
    public function savedSelections(): array
    {
        return app(FwpmSelectionService::class)->loadSelections(auth()->user(), $this->semester);
    }

    /** @return Collection<int, \App\Models\UserSelection> */
    #[Computed]
    public function savedSelectionsWithStatus(): Collection
    {
        return app(FwpmSelectionService::class)->loadSelectionsWithStatus(auth()->user(), $this->semester);
    }

    /**
     * @param  array<int, string>  $orderedFwpmIds
     */
    public function saveSelections(array $orderedFwpmIds, int $desiredCount): void
    {
        if (! $this->isSelectionOpen) {
            session()->flash('error', __('Selection period is closed.'));

            return;
        }

        $service = app(FwpmSelectionService::class);
        $bounds = $service->getChoiceBounds($desiredCount);
        $range = $service->getDesiredCountRange();

        if ($desiredCount < $range['min'] || $desiredCount > $range['max']) {
            session()->flash('error', __('Invalid desired count.'));

            return;
        }

        if (count($orderedFwpmIds) < $bounds['min'] || count($orderedFwpmIds) > $bounds['max']) {
            session()->flash('error', __('Invalid number of choices.'));

            return;
        }

        if (count($orderedFwpmIds) !== count(array_unique($orderedFwpmIds))) {
            session()->flash('error', __('Duplicate selections are not allowed.'));

            return;
        }

        $publishedCount = Fwpm::query()
            ->where('semester_id', $this->semester->id)
            ->where('status', ElectiveStatus::Published)
            ->whereIn('id', $orderedFwpmIds)
            ->count();

        if ($publishedCount !== count($orderedFwpmIds)) {
            session()->flash('error', __('Invalid FWPM selections.'));

            return;
        }

        $service->saveSelections(auth()->user(), $this->semester, $orderedFwpmIds, $desiredCount);

        unset($this->savedSelections);

        $this->dispatch('selections-saved');

        Flux::toast(
            variant: 'success',
            heading: __('Saved'),
            text: __('Selection saved successfully.'),
        );
    }

    public function render()
    {
        return view('livewire.fwpm.fwpm-index')
            ->title(__('FWPM Selection').' — '.$this->semester->getLabel());
    }
}
