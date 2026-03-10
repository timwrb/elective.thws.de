<?php

namespace App\Livewire\ResearchProject;

use App\Enums\EnrollmentStatus;
use App\Enums\EnrollmentType;
use App\Models\ResearchProject;
use App\Models\Semester;
use App\Models\User;
use App\Services\SemesterService;
use App\Settings\ResearchProjectSettings;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Join Research Project')]
class Join extends Component
{
    public ResearchProject $researchProject;

    public string $token;

    public function mount(ResearchProject $researchProject, string $token): void
    {
        if ($researchProject->invite_token !== $token) {
            abort(404);
        }

        $this->researchProject = $researchProject->load(['professor', 'creator']);
        $this->token = $token;
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

    #[Computed]
    public function isAlreadyMember(): bool
    {
        /** @var User $user */
        $user = auth()->user();
        $semester = $this->currentSemester;

        if (! $semester instanceof Semester) {
            return false;
        }

        return $this->researchProject->isUserMember($user, $semester);
    }

    #[Computed]
    public function isEnrolledElsewhere(): bool
    {
        if ($this->isAlreadyMember) {
            return false;
        }

        /** @var User $user */
        $user = auth()->user();
        $semester = $this->currentSemester;

        if (! $semester instanceof Semester) {
            return false;
        }

        return ResearchProject::isUserEnrolledInAny($user, $semester);
    }

    public function join(): void
    {
        /** @var User $user */
        $user = auth()->user();
        $semester = $this->currentSemester;

        if (! $this->settings->applicationOpen) {
            $this->addError('join', 'Applications are currently closed.');

            return;
        }

        if ($semester instanceof Semester && $this->researchProject->isUserMember($user, $semester)) {
            $this->addError('join', 'You are already a member of this project.');

            return;
        }

        if ($semester instanceof Semester && ResearchProject::isUserEnrolledInAny($user, $semester)) {
            $this->addError('join', 'You are already enrolled in another research project this semester.');

            return;
        }

        if ($semester instanceof Semester && $this->researchProject->isFull($semester)) {
            $this->addError('join', 'This project has reached its maximum number of students.');

            return;
        }

        $this->researchProject->enrollments()->create([
            'user_id' => $user->id,
            'semester_id' => $semester?->id,
            'elective_type' => ResearchProject::class,
            'enrollment_type' => EnrollmentType::Direct,
            'status' => EnrollmentStatus::Pending,
        ]);

        $this->redirect(route('research-projects.show', $this->researchProject), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.research-project.join');
    }
}
