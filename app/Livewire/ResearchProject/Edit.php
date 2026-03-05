<?php

namespace App\Livewire\ResearchProject;

use App\Models\ResearchProject;
use App\Models\User;
use App\Settings\ResearchProjectSettings;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Edit Research Project')]
class Edit extends Component
{
    public ResearchProject $researchProject;

    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('nullable|string')]
    public string $description = '';

    #[Validate('nullable|exists:users,id')]
    public ?string $professorId = null;

    public function mount(ResearchProject $researchProject): void
    {
        /** @var User $user */
        $user = auth()->user();

        if (! $researchProject->isCreatedBy($user)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $this->researchProject = $researchProject;
        $this->title = $researchProject->title;
        $this->description = $researchProject->description ?? '';
        $this->professorId = $researchProject->professor_id;
    }

    #[Computed]
    public function settings(): ResearchProjectSettings
    {
        return app(ResearchProjectSettings::class);
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
        /** @var User $user */
        $user = auth()->user();

        if (! $this->researchProject->isCreatedBy($user)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $this->validate();

        $this->researchProject->update([
            'title' => $this->title,
            'description' => $this->description ?: null,
            'professor_id' => $this->professorId,
        ]);

        $this->redirect(route('research-projects.show', $this->researchProject), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.research-project.edit');
    }
}
