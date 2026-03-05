<?php

namespace App\Livewire\ResearchProject;

use App\Models\ResearchProject;
use App\Models\Semester;
use App\Services\SemesterService;
use App\Settings\ResearchProjectSettings;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Research Projects')]
class Index extends Component
{
    #[Computed]
    public function currentSemester(): ?Semester
    {
        return app(SemesterService::class)->getCurrentSemester();
    }

    #[Computed]
    public function settings(): ResearchProjectSettings
    {
        return app(ResearchProjectSettings::class);
    }

    #[Computed]
    public function isEnrolledInAnyProject(): bool
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $semester = $this->currentSemester;

        if (! $semester instanceof Semester) {
            return false;
        }

        return ResearchProject::isUserEnrolledInAny($user, $semester);
    }

    /** @return Collection<int, ResearchProject> */
    #[Computed]
    public function projects(): Collection
    {
        $user = auth()->user();
        $semester = $this->currentSemester;

        return ResearchProject::query()
            ->whereHas('enrollments', function ($q) use ($user, $semester): void {
                $q->where('user_id', $user->id)
                    ->when($semester instanceof Semester, fn ($q) => $q->where('semester_id', $semester->id))
                    ->whereIn('status', ['pending', 'confirmed']);
            })
            ->with(['professor', 'creator', 'enrollments.user'])
            ->get();
    }

    public function render(): View
    {
        return view('livewire.research-project.index');
    }
}
