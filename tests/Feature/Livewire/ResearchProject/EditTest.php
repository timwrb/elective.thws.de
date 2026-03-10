<?php

use App\Enums\EnrollmentStatus;
use App\Enums\EnrollmentType;
use App\Livewire\ResearchProject\Edit;
use App\Models\ResearchProject;
use App\Models\Semester;
use App\Models\User;
use App\Models\UserSelection;
use App\Settings\ResearchProjectSettings;
use Illuminate\Support\Facades\Date;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Date::setTestNow('2024-01-15 12:00:00');
    $this->semester = Semester::factory()->winter()->year(2023)->create();
    $this->creator = User::factory()->create();

    Role::firstOrCreate(['name' => 'professor', 'guard_name' => 'web']);

    $settings = app(ResearchProjectSettings::class);
    $settings->applicationOpen = true;
    $settings->maxStudentsPerProject = 3;
    $settings->defaultCredits = 10;
    $settings->save();
});

it('redirects guests to login', function (): void {
    $project = ResearchProject::factory()->withInviteToken()->withCreator($this->creator)->create();

    $this->get(route('research-projects.edit', $project))
        ->assertRedirect(route('login'));
});

it('renders for the creator', function (): void {
    $project = ResearchProject::factory()->withCreator($this->creator)->withInviteToken()->create(['title' => 'Editable']);

    $this->actingAs($this->creator)
        ->get(route('research-projects.edit', $project))
        ->assertOk();
});

it('returns 403 for non-creator members', function (): void {
    $project = ResearchProject::factory()->withInviteToken()->create();
    $member = User::factory()->create();

    UserSelection::factory()->create([
        'user_id' => $member->id,
        'semester_id' => $this->semester->id,
        'elective_type' => ResearchProject::class,
        'elective_choice_id' => $project->id,
        'enrollment_type' => EnrollmentType::Direct,
        'status' => EnrollmentStatus::Confirmed,
    ]);

    $this->actingAs($member)
        ->get(route('research-projects.edit', $project))
        ->assertForbidden();
});

it('updates project fields', function (): void {
    $project = ResearchProject::factory()->withCreator($this->creator)->withInviteToken()->create(['title' => 'Old Title']);

    Livewire::actingAs($this->creator)
        ->test(Edit::class, ['researchProject' => $project])
        ->set('title', 'New Title')
        ->set('description', 'Updated description')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect();

    expect($project->fresh()->title)->toBe('New Title')
        ->and($project->fresh()->description)->toBe('Updated description');
});

it('does not change the invite token on edit', function (): void {
    $project = ResearchProject::factory()->withCreator($this->creator)->withInviteToken()->create();
    $original = $project->invite_token;

    Livewire::actingAs($this->creator)
        ->test(Edit::class, ['researchProject' => $project])
        ->set('title', 'Updated')
        ->call('save');

    expect($project->fresh()->invite_token)->toBe($original);
});
