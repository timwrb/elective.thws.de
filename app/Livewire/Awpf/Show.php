<?php

namespace App\Livewire\Awpf;

use App\Enums\ElectiveStatus;
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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Layout('layouts.app')]
#[Title('Course Details')]
class Show extends Component
{
    public Awpf $awpf;

    public function mount(Awpf $awpf): void
    {
        if ($awpf->status !== ElectiveStatus::Published) {
            throw new NotFoundHttpException;
        }

        $this->awpf = $awpf->load(['professor', 'schedules']);
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

    /** @return Collection<int, UserSelection> */
    #[Computed]
    public function userSelections(): Collection
    {
        $semester = $this->currentSemester;

        if (! $semester instanceof Semester) {
            return collect();
        }

        $user = auth()->user();

        if (! $user instanceof User) {
            return collect();
        }

        return $user->selections()
            ->forSemester($semester)
            ->awpf()
            ->get();
    }

    #[Computed]
    public function isInUserSelection(): bool
    {
        return $this->userSelections
            ->contains('elective_choice_id', $this->awpf->id);
    }

    #[Computed]
    public function userPriority(): ?int
    {
        $selection = $this->userSelections
            ->firstWhere('elective_choice_id', $this->awpf->id);

        return $selection?->getPriorityOrder();
    }

    public function render(): View
    {
        return view('livewire.awpf.show');
    }
}
