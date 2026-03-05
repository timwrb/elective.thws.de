<?php

use App\Enums\EnrollmentStatus;
use App\Enums\EnrollmentType;
use App\Livewire\ResearchProject\Create;
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
    $this->user = User::factory()->create();

    Role::firstOrCreate(['name' => 'professor', 'guard_name' => 'web']);

    $settings = app(ResearchProjectSettings::class);
    $settings->applicationOpen = true;
    $settings->maxStudentsPerProject = 3;
    $settings->defaultCredits = 10;
    $settings->save();
});

it('redirects guests to login', function (): void {
    $this->get(route('research-projects.create'))
        ->assertRedirect(route('login'));
});

it('renders the create page when application is open', function (): void {
    $this->actingAs($this->user)
        ->get(route('research-projects.create'))
        ->assertOk();
});

it('returns 403 when application is closed', function (): void {
    $settings = app(ResearchProjectSettings::class);
    $settings->applicationOpen = false;
    $settings->save();

    $this->actingAs($this->user)
        ->get(route('research-projects.create'))
        ->assertForbidden();
});

it('creates a project with an invite token', function (): void {
    Livewire::actingAs($this->user)
        ->test(Create::class)
        ->set('title', 'My Research Topic')
        ->set('description', 'A detailed description')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect();

    $project = ResearchProject::query()->where('title', 'My Research Topic')->first();

    expect($project)->not->toBeNull()
        ->and($project->invite_token)->toHaveLength(32)
        ->and($project->creator_id)->toBe($this->user->id)
        ->and($project->credits)->toBe(10)
        ->and($project->max_students)->toBe(3);
});

it('enrolls the creator as a confirmed direct member', function (): void {
    Livewire::actingAs($this->user)
        ->test(Create::class)
        ->set('title', 'Creator Member Test')
        ->call('save');

    $project = ResearchProject::query()->where('title', 'Creator Member Test')->first();

    $enrollment = $project->enrollments()
        ->where('user_id', $this->user->id)
        ->where('semester_id', $this->semester->id)
        ->first();

    expect($enrollment)->not->toBeNull()
        ->and($enrollment->status)->toBe(EnrollmentStatus::Confirmed)
        ->and($enrollment->enrollment_type)->toBe(EnrollmentType::Direct);
});

it('validates required fields', function (): void {
    Livewire::actingAs($this->user)
        ->test(Create::class)
        ->set('title', '')
        ->call('save')
        ->assertHasErrors(['title' => 'required']);
});

it('returns 403 when user is already enrolled in another project', function (): void {
    $other = ResearchProject::factory()->create();

    UserSelection::factory()->create([
        'user_id' => $this->user->id,
        'semester_id' => $this->semester->id,
        'elective_type' => ResearchProject::class,
        'elective_choice_id' => $other->id,
        'enrollment_type' => EnrollmentType::Direct,
        'status' => EnrollmentStatus::Confirmed,
    ]);

    $this->actingAs($this->user)
        ->get(route('research-projects.create'))
        ->assertForbidden();
});
