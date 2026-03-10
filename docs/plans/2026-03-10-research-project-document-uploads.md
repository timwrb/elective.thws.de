# Research Project Document Uploads — Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Allow confirmed members of a research project to upload, view, and delete documents (max 10, 10 MB each, pdf/doc/docx/xls/xlsx/ppt/pptx).

**Architecture:** A dedicated Livewire component `ResearchProject\Documents` handles all file logic and is embedded in the show page. Spatie MediaLibrary (already installed) stores files in the existing `attachments` collection on the `public` disk. Uploader identity is tracked via `custom_properties->uploaded_by` on each media record.

**Tech Stack:** Livewire 4, Flux UI Pro v2, Spatie MediaLibrary v4, Pest 4

---

### Task 1: Add `isConfirmedMember` helper to `ResearchProject`

**Files:**
- Modify: `app/Models/ResearchProject.php`
- Test: `tests/Feature/Models/ResearchProjectTest.php`

**Step 1: Write the failing test**

Open `tests/Feature/Models/ResearchProjectTest.php` and add:

```php
it('identifies confirmed members correctly', function (): void {
    $semester = Semester::factory()->winter()->year(2023)->create();
    $project = ResearchProject::factory()->withInviteToken()->create();
    $user = User::factory()->create();

    expect($project->isConfirmedMember($user, $semester))->toBeFalse();

    UserSelection::factory()->create([
        'user_id' => $user->id,
        'semester_id' => $semester->id,
        'elective_type' => ResearchProject::class,
        'elective_choice_id' => $project->id,
        'enrollment_type' => \App\Enums\EnrollmentType::Direct,
        'status' => \App\Enums\EnrollmentStatus::Pending,
    ]);
    expect($project->isConfirmedMember($user, $semester))->toBeFalse();

    UserSelection::query()
        ->where('user_id', $user->id)
        ->update(['status' => \App\Enums\EnrollmentStatus::Confirmed]);
    expect($project->fresh()->isConfirmedMember($user, $semester))->toBeTrue();
});
```

**Step 2: Run to verify it fails**

```bash
php artisan test --compact --filter="identifies confirmed members correctly"
```

Expected: FAIL — method not found.

**Step 3: Add the method to `ResearchProject`**

In `app/Models/ResearchProject.php`, after the `isUserMember()` method, add:

```php
public function isConfirmedMember(User $user, Semester $semester): bool
{
    return $this->enrollments()
        ->forUser($user)
        ->forSemester($semester)
        ->where('status', 'confirmed')
        ->exists();
}
```

**Step 4: Run tests to verify they pass**

```bash
php artisan test --compact --filter="identifies confirmed members correctly"
```

Expected: PASS

**Step 5: Format**

```bash
vendor/bin/pint --dirty --format agent
```

---

### Task 2: Create the `Documents` Livewire component class

**Files:**
- Create: `app/Livewire/ResearchProject/Documents.php`
- Test: `tests/Feature/Livewire/ResearchProject/DocumentsTest.php`

**Step 1: Create the test file**

```bash
php artisan make:test --pest Livewire/ResearchProject/DocumentsTest --no-interaction
```

**Step 2: Write failing tests**

Replace the contents of `tests/Feature/Livewire/ResearchProject/DocumentsTest.php`:

```php
<?php

use App\Enums\EnrollmentStatus;
use App\Enums\EnrollmentType;
use App\Livewire\ResearchProject\Documents;
use App\Models\ResearchProject;
use App\Models\Semester;
use App\Models\User;
use App\Models\UserSelection;
use App\Settings\ResearchProjectSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Date::setTestNow('2024-01-15 12:00:00');
    Storage::fake('public');
    $this->semester = Semester::factory()->winter()->year(2023)->create();
    $this->user = User::factory()->create();
    Role::firstOrCreate(['name' => 'professor', 'guard_name' => 'web']);

    $settings = app(ResearchProjectSettings::class);
    $settings->applicationOpen = true;
    $settings->maxStudentsPerProject = 5;
    $settings->save();
});

function enrollConfirmed(User $user, ResearchProject $project, Semester $semester): UserSelection
{
    return UserSelection::factory()->create([
        'user_id' => $user->id,
        'semester_id' => $semester->id,
        'elective_type' => ResearchProject::class,
        'elective_choice_id' => $project->id,
        'enrollment_type' => EnrollmentType::Direct,
        'status' => EnrollmentStatus::Confirmed,
    ]);
}

it('renders the documents component for a confirmed member', function (): void {
    $project = ResearchProject::factory()->withInviteToken()->create();
    enrollConfirmed($this->user, $project, $this->semester);

    Livewire::actingAs($this->user)
        ->test(Documents::class, ['project' => $project])
        ->assertOk();
});

it('allows a confirmed member to upload a document', function (): void {
    $project = ResearchProject::factory()->withInviteToken()->create();
    enrollConfirmed($this->user, $project, $this->semester);

    $file = UploadedFile::fake()->create('report.pdf', 1024, 'application/pdf');

    Livewire::actingAs($this->user)
        ->test(Documents::class, ['project' => $project])
        ->set('file', $file)
        ->call('upload')
        ->assertHasNoErrors();

    expect($project->fresh()->getMedia('attachments'))->toHaveCount(1);
});

it('rejects upload from a non-member', function (): void {
    $project = ResearchProject::factory()->withInviteToken()->create();
    $outsider = User::factory()->create();

    $file = UploadedFile::fake()->create('report.pdf', 1024, 'application/pdf');

    Livewire::actingAs($outsider)
        ->test(Documents::class, ['project' => $project])
        ->set('file', $file)
        ->call('upload')
        ->assertForbidden();
});

it('rejects upload from a pending member', function (): void {
    $project = ResearchProject::factory()->withInviteToken()->create();
    UserSelection::factory()->create([
        'user_id' => $this->user->id,
        'semester_id' => $this->semester->id,
        'elective_type' => ResearchProject::class,
        'elective_choice_id' => $project->id,
        'enrollment_type' => EnrollmentType::Direct,
        'status' => EnrollmentStatus::Pending,
    ]);

    $file = UploadedFile::fake()->create('report.pdf', 1024, 'application/pdf');

    Livewire::actingAs($this->user)
        ->test(Documents::class, ['project' => $project])
        ->set('file', $file)
        ->call('upload')
        ->assertForbidden();
});

it('rejects files exceeding 10 MB', function (): void {
    $project = ResearchProject::factory()->withInviteToken()->create();
    enrollConfirmed($this->user, $project, $this->semester);

    $file = UploadedFile::fake()->create('huge.pdf', 11264, 'application/pdf'); // 11 MB

    Livewire::actingAs($this->user)
        ->test(Documents::class, ['project' => $project])
        ->set('file', $file)
        ->call('upload')
        ->assertHasErrors(['file']);
});

it('rejects disallowed file types', function (): void {
    $project = ResearchProject::factory()->withInviteToken()->create();
    enrollConfirmed($this->user, $project, $this->semester);

    $file = UploadedFile::fake()->create('script.exe', 100, 'application/octet-stream');

    Livewire::actingAs($this->user)
        ->test(Documents::class, ['project' => $project])
        ->set('file', $file)
        ->call('upload')
        ->assertHasErrors(['file']);
});

it('rejects upload when 10 documents already exist', function (): void {
    $project = ResearchProject::factory()->withInviteToken()->create();
    enrollConfirmed($this->user, $project, $this->semester);

    // Add 10 documents to the project
    for ($i = 0; $i < 10; $i++) {
        $tmpFile = UploadedFile::fake()->create("doc{$i}.pdf", 100, 'application/pdf');
        $project->addMedia($tmpFile)->toMediaCollection('attachments');
    }

    $file = UploadedFile::fake()->create('eleventh.pdf', 100, 'application/pdf');

    Livewire::actingAs($this->user)
        ->test(Documents::class, ['project' => $project])
        ->set('file', $file)
        ->call('upload')
        ->assertHasErrors(['file']);
});

it('allows the uploader to delete their own document', function (): void {
    $project = ResearchProject::factory()->withInviteToken()->create();
    enrollConfirmed($this->user, $project, $this->semester);

    $file = UploadedFile::fake()->create('report.pdf', 100, 'application/pdf');
    $media = $project->addMedia($file)
        ->withCustomProperties(['uploaded_by' => $this->user->id])
        ->toMediaCollection('attachments');

    Livewire::actingAs($this->user)
        ->test(Documents::class, ['project' => $project])
        ->call('deleteDocument', $media->id)
        ->assertHasNoErrors();

    expect($project->fresh()->getMedia('attachments'))->toHaveCount(0);
});

it('prevents a non-uploader from deleting a document', function (): void {
    $project = ResearchProject::factory()->withInviteToken()->create();
    enrollConfirmed($this->user, $project, $this->semester);

    $other = User::factory()->create();
    enrollConfirmed($other, $project, $this->semester);

    $file = UploadedFile::fake()->create('report.pdf', 100, 'application/pdf');
    $media = $project->addMedia($file)
        ->withCustomProperties(['uploaded_by' => $other->id])
        ->toMediaCollection('attachments');

    Livewire::actingAs($this->user)
        ->test(Documents::class, ['project' => $project])
        ->call('deleteDocument', $media->id)
        ->assertForbidden();

    expect($project->fresh()->getMedia('attachments'))->toHaveCount(1);
});
```

**Step 3: Run to verify tests fail**

```bash
php artisan test --compact --filter=DocumentsTest
```

Expected: FAIL — component class not found.

**Step 4: Create the component class**

```bash
php artisan make:livewire ResearchProject/Documents --no-interaction
```

Replace the generated `app/Livewire/ResearchProject/Documents.php` with:

```php
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
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Documents extends Component
{
    use WithFileUploads;

    public ResearchProject $project;

    #[Validate([
        'file' => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx'],
    ])]
    public $file = null;

    public ?int $confirmDeleteId = null;

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

    public function upload(): void
    {
        /** @var User $user */
        $user = auth()->user();
        $semester = $this->currentSemester;

        if (! $semester instanceof Semester || ! $this->project->isConfirmedMember($user, $semester)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        if ($this->project->getMedia('attachments')->count() >= 10) {
            $this->addError('file', __('The project has reached the maximum of 10 documents.'));

            return;
        }

        $this->validate();

        $this->project
            ->addMedia($this->file->getRealPath())
            ->usingName($this->file->getClientOriginalName())
            ->usingFileName($this->file->getClientOriginalName())
            ->withCustomProperties(['uploaded_by' => $user->id])
            ->toMediaCollection('attachments');

        $this->file = null;
        unset($this->documents);

        $this->dispatch('document-uploaded');
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
        unset($this->documents);
    }

    public function render(): View
    {
        return view('livewire.research-project.documents');
    }
}
```

**Step 5: Run the tests**

```bash
php artisan test --compact --filter=DocumentsTest
```

Expected: Most pass. The upload test may fail because `addMedia` via `getRealPath()` needs the real temp path from `WithFileUploads`. If so, replace in `upload()`:

```php
$this->project
    ->addMedia($this->file->getRealPath())
    ->usingName($this->file->getClientOriginalName())
    ->usingFileName($this->file->getClientOriginalName())
    ->withCustomProperties(['uploaded_by' => $user->id])
    ->toMediaCollection('attachments');
```

with Livewire's approach using the stored temp path directly (already done above).

**Step 6: Format**

```bash
vendor/bin/pint --dirty --format agent
```

---

### Task 3: Create the `documents.blade.php` view

**Files:**
- Create: `resources/views/livewire/research-project/documents.blade.php`

**Step 1: Create the view**

Create `resources/views/livewire/research-project/documents.blade.php`:

```blade
<div class="space-y-4">

    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Documents') }}</flux:heading>
        <flux:text size="sm" class="text-zinc-400">
            {{ $this->documents->count() }} / 10
        </flux:text>
    </div>

    {{-- Upload form --}}
    @if($this->documents->count() < 10)
        <form wire:submit="upload" class="flex items-start gap-3">
            <div class="flex-1">
                <flux:input
                    type="file"
                    wire:model="file"
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
                    size="sm"
                />
                @error('file')
                    <flux:text size="sm" class="text-red-500 mt-1">{{ $message }}</flux:text>
                @enderror
            </div>
            <flux:button type="submit" size="sm" variant="filled" icon="arrow-up-tray" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="upload">{{ __('Upload') }}</span>
                <span wire:loading wire:target="upload">{{ __('Uploading…') }}</span>
            </flux:button>
        </form>
    @else
        <flux:callout variant="warning" icon="exclamation-triangle">
            <flux:callout.heading>{{ __('Upload limit reached') }}</flux:callout.heading>
            <flux:callout.text>{{ __('This project has reached the maximum of 10 documents. Delete an existing document to upload a new one.') }}</flux:callout.text>
        </flux:callout>
    @endif

    {{-- Document list --}}
    @if($this->documents->isEmpty())
        <div class="flex flex-col items-center justify-center py-10 text-center border border-dashed border-zinc-200 dark:border-zinc-700 rounded-lg">
            <flux:icon name="document" class="size-8 text-zinc-300 dark:text-zinc-600 mb-2" />
            <flux:text class="text-zinc-400">{{ __('No documents uploaded yet.') }}</flux:text>
        </div>
    @else
        <div class="divide-y divide-zinc-100 dark:divide-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-lg overflow-hidden">
            @foreach($this->documents as $document)
                <div class="flex items-center justify-between gap-3 px-4 py-3 bg-white dark:bg-zinc-900">
                    <div class="flex items-center gap-3 min-w-0">
                        <flux:icon name="document-text" class="size-5 text-zinc-400 shrink-0" />
                        <div class="min-w-0">
                            <flux:text class="font-medium text-sm truncate">{{ $document->file_name }}</flux:text>
                            <flux:text size="sm" class="text-zinc-400">
                                {{ number_format($document->size / 1024 / 1024, 2) }} MB
                                · {{ $document->created_at->diffForHumans() }}
                            </flux:text>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <flux:button
                            :href="$document->getUrl()"
                            target="_blank"
                            rel="noopener noreferrer"
                            size="sm"
                            variant="ghost"
                            icon="arrow-down-tray"
                        />
                        @if($document->getCustomProperty('uploaded_by') === auth()->id())
                            <flux:button
                                wire:click="confirmDelete({{ $document->id }})"
                                size="sm"
                                variant="ghost"
                                icon="trash"
                                class="text-red-500 hover:text-red-600"
                            />
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Delete confirmation modal --}}
    <flux:modal wire:model="confirmDeleteId" name="confirm-delete-document">
        <div class="space-y-4">
            <flux:heading>{{ __('Delete Document') }}</flux:heading>
            <flux:text>{{ __('Are you sure you want to delete this document? This action cannot be undone.') }}</flux:text>
            <div class="flex justify-end gap-2">
                <flux:button wire:click="$set('confirmDeleteId', null)" variant="ghost">{{ __('Cancel') }}</flux:button>
                <flux:button wire:click="deleteDocument({{ $confirmDeleteId ?? 0 }})" variant="danger">{{ __('Delete') }}</flux:button>
            </div>
        </div>
    </flux:modal>

</div>
```

**Step 2: Run the test suite**

```bash
php artisan test --compact --filter=DocumentsTest
```

Expected: All pass.

---

### Task 4: Embed Documents component in the show page

**Files:**
- Modify: `resources/views/livewire/research-project/show.blade.php`

**Step 1: Add the Documents component after the Team section**

In `resources/views/livewire/research-project/show.blade.php`, after the closing `</div>` of the team members section and before `</div>` closing the main content column (`lg:col-span-2`), add a separator and the component:

```blade
            <flux:separator />

            {{-- Documents --}}
            <livewire:research-project.documents :project="$researchProject" />
```

The result around line 73 should look like:

```blade
            </div>
        </div>

        <flux:separator />

        {{-- Documents --}}
        <livewire:research-project.documents :project="$researchProject" />

    </div>

    {{-- Sidebar --}}
```

**Step 2: Run show page tests to ensure nothing broke**

```bash
php artisan test --compact --filter=ShowTest
```

Expected: All pass.

---

### Task 5: Add German translations

**Files:**
- Modify: `lang/de.json`

**Step 1: Add translation keys**

Add these entries to `lang/de.json` in alphabetical order:

```json
"Are you sure you want to delete this document? This action cannot be undone.": "Bist du sicher, dass du dieses Dokument löschen möchtest? Diese Aktion kann nicht rückgängig gemacht werden.",
"Delete Document": "Dokument löschen",
"Documents": "Dokumente",
"No documents uploaded yet.": "Noch keine Dokumente hochgeladen.",
"The project has reached the maximum of 10 documents.": "Das Projekt hat das Maximum von 10 Dokumenten erreicht.",
"Upload": "Hochladen",
"Upload Document": "Dokument hochladen",
"Upload limit reached": "Upload-Limit erreicht",
"Uploading…": "Wird hochgeladen…",
"This project has reached the maximum of 10 documents. Delete an existing document to upload a new one.": "Dieses Projekt hat das Maximum von 10 Dokumenten erreicht. Lösche ein vorhandenes Dokument, um ein neues hochzuladen."
```

---

### Task 6: Run full test suite

**Step 1: Run all research project tests**

```bash
php artisan test --compact --filter=ResearchProject
```

Expected: All pass.

**Step 2: Format all dirty files**

```bash
vendor/bin/pint --dirty --format agent
```