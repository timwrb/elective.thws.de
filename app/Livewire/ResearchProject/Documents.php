<?php

namespace App\Livewire\ResearchProject;

use App\Models\ResearchProject;
use App\Models\Semester;
use App\Models\User;
use App\Services\SemesterService;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Documents extends Component
{
    use WithFileUploads;

    public ResearchProject $project;

    public mixed $file = null;

    public ?int $confirmDeleteId = null;

    protected function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx'],
        ];
    }

    #[Computed]
    public function currentSemester(): ?Semester
    {
        return app(SemesterService::class)->getCurrentSemester();
    }

    /** @return Collection<int, Media> */
    #[Computed]
    public function documents(): Collection
    {
        return $this->project->getMedia('attachments')->sortByDesc('created_at');
    }

    public function storeDocument(): void
    {
        /** @var User $user */
        $user = auth()->user();
        $semester = $this->currentSemester;

        if (! $semester instanceof Semester || ! $this->project->isConfirmedMember($user, $semester)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        if ($this->documents->count() >= 10) {
            $this->addError('file', __('The project has reached the maximum of 10 documents.'));
            $this->file = null;

            return;
        }

        $this->validate();

        $this->project
            ->addMedia($this->file->path())
            ->usingName($this->file->getClientOriginalName())
            ->usingFileName($this->file->getClientOriginalName())
            ->withCustomProperties(['uploaded_by' => $user->id])
            ->toMediaCollection('attachments');

        $this->file = null;
        $this->project->unsetRelation('media');
        unset($this->documents);

        $this->dispatch('document-uploaded');
    }

    public function removeFile(): void
    {
        $this->file = null;
    }

    public function confirmDelete(int $mediaId): void
    {
        $this->confirmDeleteId = $mediaId;
    }

    public function deleteDocument(int $mediaId): void
    {
        /** @var User $user */
        $user = auth()->user();

        $media = $this->project->getMedia('attachments')
            ->firstWhere('id', $mediaId);

        if (! $media || $media->getCustomProperty('uploaded_by') !== $user->id) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $media->delete();
        $this->confirmDeleteId = null;
        $this->project->unsetRelation('media');
        unset($this->documents);
    }

    public function render(): View
    {
        return view('livewire.research-project.documents');
    }
}
