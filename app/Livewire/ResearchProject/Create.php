<?php

namespace App\Livewire\ResearchProject;

use App\Enums\ElectiveStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\EnrollmentType;
use App\Models\ResearchProject;
use App\Models\Semester;
use App\Models\User;
use App\Services\SemesterService;
use App\Settings\ResearchProjectSettings;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Create Research Project')]
class Create extends Component
{
    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('nullable|string')]
    public string $description = '';

    #[Validate('nullable|exists:users,id')]
    public ?string $professorId = null;

    public function mount(): void
    {
        if (! app(ResearchProjectSettings::class)->applicationOpen) {
            abort(Response::HTTP_FORBIDDEN);
        }

        /** @var User $user */
        $user = auth()->user();
        $semester = $this->currentSemester;

        if ($semester instanceof Semester && ResearchProject::isUserEnrolledInAny($user, $semester)) {
            abort(Response::HTTP_FORBIDDEN);
        }
    }

    #[Computed]
    public function settings(): ResearchProjectSettings
    {
        return app(ResearchProjectSettings::class);
    }

    #[Computed]
    public function currentSemester(): ?Semester
    {
        return app(SemesterService::class)->getCurrentSemester();
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    #[Computed]
    public function professorOptions(): array
    {
        return User::query()
            ->role('professor')
            ->orderBy('surname')
            ->get()
            ->map(fn (User $user): array => [
                'value' => $user->id,
                'label' => $user->full_name,
            ])
            ->all();
    }

    public function save(): void
    {
        if (! $this->settings->applicationOpen) {
            abort(Response::HTTP_FORBIDDEN);
        }

        /** @var User $user */
        $user = auth()->user();
        $semester = $this->currentSemester;

        if ($semester instanceof Semester && ResearchProject::isUserEnrolledInAny($user, $semester)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $this->validate();

        $project = ResearchProject::query()->create([
            'title' => $this->title,
            'description' => $this->description ?: null,
            'credits' => $this->settings->defaultCredits,
            'max_students' => $this->settings->maxStudentsPerProject,
            'professor_id' => $this->professorId,
            'creator_id' => $user->id,
            'status' => ElectiveStatus::Published,
            'semester_id' => $this->currentSemester?->id,
        ]);

        $project->generateInviteToken();

        $project->enrollments()->create([
            'user_id' => $user->id,
            'semester_id' => $this->currentSemester?->id,
            'elective_type' => ResearchProject::class,
            'enrollment_type' => EnrollmentType::Direct,
            'status' => EnrollmentStatus::Confirmed,
        ]);

        $this->redirect(route('research-projects.show', $project), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.research-project.create');
    }
}
