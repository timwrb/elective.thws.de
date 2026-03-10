<?php

use App\Enums\EnrollmentStatus;
use App\Enums\EnrollmentType;
use App\Livewire\ResearchProject\Join;
use App\Models\ResearchProject;
use App\Models\Semester;
use App\Models\User;
use App\Models\UserSelection;
use App\Settings\ResearchProjectSettings;
use Illuminate\Support\Facades\Date;
use Livewire\Livewire;

beforeEach(function (): void {
    Date::setTestNow('2024-01-15 12:00:00');
    $this->semester = Semester::factory()->winter()->year(2023)->create();
    $this->user = User::factory()->create();

    $settings = app(ResearchProjectSettings::class);
    $settings->applicationOpen = true;
    $settings->maxStudentsPerProject = 5;
    $settings->save();
});

it('redirects guests to login', function (): void {
    $project = ResearchProject::factory()->withInviteToken()->create();

    $this->get(route('research-projects.join', ['researchProject' => $project, 'token' => $project->invite_token]))
        ->assertRedirect(route('login'));
});

it('renders the join page with a valid token', function (): void {
    $project = ResearchProject::factory()->withInviteToken()->create(['title' => 'Join Me']);

    $this->actingAs($this->user)
        ->get(route('research-projects.join', ['researchProject' => $project, 'token' => $project->invite_token]))
        ->assertOk();
});

it('returns 404 for an invalid token', function (): void {
    $project = ResearchProject::factory()->withInviteToken()->create();

    $this->actingAs($this->user)
        ->get(route('research-projects.join', ['researchProject' => $project, 'token' => 'wrong-token']))
        ->assertNotFound();
});

it('joins successfully with a valid token', function (): void {
    $project = ResearchProject::factory()->withInviteToken()->create(['max_students' => 5]);

    Livewire::actingAs($this->user)
        ->test(Join::class, ['researchProject' => $project, 'token' => $project->invite_token])
        ->call('join')
        ->assertRedirect(route('research-projects.show', $project));

    $enrollment = UserSelection::query()
        ->where('user_id', $this->user->id)
        ->where('elective_type', ResearchProject::class)
        ->where('elective_choice_id', $project->id)
        ->first();

    expect($enrollment)->not->toBeNull()
        ->and($enrollment->status)->toBe(EnrollmentStatus::Pending)
        ->and($enrollment->enrollment_type)->toBe(EnrollmentType::Direct);
});

it('prevents joining when application is closed', function (): void {
    $settings = app(ResearchProjectSettings::class);
    $settings->applicationOpen = false;
    $settings->save();

    $project = ResearchProject::factory()->withInviteToken()->create();

    Livewire::actingAs($this->user)
        ->test(Join::class, ['researchProject' => $project, 'token' => $project->invite_token])
        ->call('join')
        ->assertHasErrors();
});

it('prevents joining a full project', function (): void {
    $project = ResearchProject::factory()->withInviteToken()->create(['max_students' => 1]);

    $existing = User::factory()->create();
    UserSelection::factory()->create([
        'user_id' => $existing->id,
        'semester_id' => $this->semester->id,
        'elective_type' => ResearchProject::class,
        'elective_choice_id' => $project->id,
        'enrollment_type' => EnrollmentType::Direct,
        'status' => EnrollmentStatus::Confirmed,
    ]);

    Livewire::actingAs($this->user)
        ->test(Join::class, ['researchProject' => $project, 'token' => $project->invite_token])
        ->call('join')
        ->assertHasErrors();
});

it('prevents duplicate enrollment', function (): void {
    $project = ResearchProject::factory()->withInviteToken()->create(['max_students' => 5]);

    UserSelection::factory()->create([
        'user_id' => $this->user->id,
        'semester_id' => $this->semester->id,
        'elective_type' => ResearchProject::class,
        'elective_choice_id' => $project->id,
        'enrollment_type' => EnrollmentType::Direct,
        'status' => EnrollmentStatus::Confirmed,
    ]);

    Livewire::actingAs($this->user)
        ->test(Join::class, ['researchProject' => $project, 'token' => $project->invite_token])
        ->call('join')
        ->assertHasErrors();
});

it('prevents joining when already enrolled in another project', function (): void {
    $other = ResearchProject::factory()->create(['max_students' => 5]);

    UserSelection::factory()->create([
        'user_id' => $this->user->id,
        'semester_id' => $this->semester->id,
        'elective_type' => ResearchProject::class,
        'elective_choice_id' => $other->id,
        'enrollment_type' => EnrollmentType::Direct,
        'status' => EnrollmentStatus::Confirmed,
    ]);

    $project = ResearchProject::factory()->withInviteToken()->create(['max_students' => 5]);

    Livewire::actingAs($this->user)
        ->test(Join::class, ['researchProject' => $project, 'token' => $project->invite_token])
        ->call('join')
        ->assertHasErrors();
});
