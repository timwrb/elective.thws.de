<?php

namespace App\Livewire\ResearchProject;

use App\Models\ResearchProject;
use App\Models\Semester;
use App\Models\User;
use App\Models\UserSelection;
use App\Services\SemesterService;
use App\Settings\ResearchProjectSettings;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Research Project')]
class Show extends Component
{
    public ResearchProject $researchProject;

    public function mount(ResearchProject $researchProject): void
    {
        /** @var User $user */
        $user = auth()->user();

        $semester = $this->currentSemester;

        $isMember = $semester instanceof Semester && $researchProject->isUserMember($user, $semester);
        $isCreator = $researchProject->isCreatedBy($user);

        if (! $isMember && ! $isCreator) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $this->researchProject = $researchProject->load(['professor', 'creator', 'semester']);
    }

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

    /** @return Collection<int, UserSelection> */
    #[Computed]
    public function members(): Collection
    {
        $semester = $this->currentSemester;

        return $this->researchProject
            ->enrollments()
            ->when($semester instanceof Semester, fn ($q) => $q->where('semester_id', $semester->id))
            ->whereIn('status', ['pending', 'confirmed'])
            ->with('user')
            ->get();
    }

    #[Computed]
    public function isCreator(): bool
    {
        /** @var User $user */
        $user = auth()->user();

        return $this->researchProject->isCreatedBy($user);
    }

    #[Computed]
    public function inviteUrl(): string
    {
        return route('research-projects.join', [
            'researchProject' => $this->researchProject,
            'token' => $this->researchProject->invite_token,
        ]);
    }

    public function regenerateToken(): void
    {
        /** @var User $user */
        $user = auth()->user();

        if (! $this->researchProject->isCreatedBy($user)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $this->researchProject->generateInviteToken();
        unset($this->inviteUrl);

        $this->dispatch('token-regenerated');
    }

    public function render(): View
    {
        return view('livewire.research-project.show');
    }
}
