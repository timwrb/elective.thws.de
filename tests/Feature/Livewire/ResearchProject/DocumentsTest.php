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

function enrollConfirmed(User $user, ResearchProject $project, Semester $semester, EnrollmentStatus $status = EnrollmentStatus::Confirmed): UserSelection
{
    return UserSelection::factory()->create([
        'user_id' => $user->id,
        'semester_id' => $semester->id,
        'elective_type' => ResearchProject::class,
        'elective_choice_id' => $project->id,
        'enrollment_type' => EnrollmentType::Direct,
        'status' => $status,
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
        ->call('storeDocument')
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
        ->call('storeDocument')
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
        ->call('storeDocument')
        ->assertForbidden();
});

it('rejects files exceeding 10 MB', function (): void {
    $project = ResearchProject::factory()->withInviteToken()->create();
    enrollConfirmed($this->user, $project, $this->semester);

    $file = UploadedFile::fake()->create('huge.pdf', 11264, 'application/pdf'); // 11 MB

    Livewire::actingAs($this->user)
        ->test(Documents::class, ['project' => $project])
        ->set('file', $file)
        ->call('storeDocument')
        ->assertHasErrors(['file']);
});

it('rejects disallowed file types', function (): void {
    $project = ResearchProject::factory()->withInviteToken()->create();
    enrollConfirmed($this->user, $project, $this->semester);

    $file = UploadedFile::fake()->create('script.exe', 100, 'application/octet-stream');

    Livewire::actingAs($this->user)
        ->test(Documents::class, ['project' => $project])
        ->set('file', $file)
        ->call('storeDocument')
        ->assertHasErrors(['file']);
});

it('rejects upload when 10 documents already exist', function (): void {
    $project = ResearchProject::factory()->withInviteToken()->create();
    enrollConfirmed($this->user, $project, $this->semester);

    for ($i = 0; $i < 10; $i++) {
        $tmpFile = UploadedFile::fake()->create("doc{$i}.pdf", 100, 'application/pdf');
        $project->addMedia($tmpFile)->toMediaCollection('attachments');
    }

    $file = UploadedFile::fake()->create('eleventh.pdf', 100, 'application/pdf');

    Livewire::actingAs($this->user)
        ->test(Documents::class, ['project' => $project])
        ->set('file', $file)
        ->call('storeDocument')
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
