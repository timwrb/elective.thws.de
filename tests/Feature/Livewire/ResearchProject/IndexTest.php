<?php

use App\Enums\EnrollmentStatus;
use App\Enums\EnrollmentType;
use App\Livewire\ResearchProject\Index;
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
});

it('redirects guests to login', function (): void {
    $this->get(route('research-projects.index'))
        ->assertRedirect(route('login'));
});

it('renders the index page for authenticated users', function (): void {
    $this->actingAs($this->user)
        ->get(route('research-projects.index'))
        ->assertOk();
});

it('shows projects the user is a member of', function (): void {
    $project = ResearchProject::factory()->published()->create(['title' => 'My Research']);

    UserSelection::factory()->create([
        'user_id' => $this->user->id,
        'semester_id' => $this->semester->id,
        'elective_type' => ResearchProject::class,
        'elective_choice_id' => $project->id,
        'enrollment_type' => EnrollmentType::Direct,
        'status' => EnrollmentStatus::Confirmed,
    ]);

    Livewire::actingAs($this->user)
        ->test(Index::class)
        ->assertSee('My Research');
});

it('does not show projects the user is not a member of', function (): void {
    ResearchProject::factory()->published()->create(['title' => 'Someone Else Project']);

    Livewire::actingAs($this->user)
        ->test(Index::class)
        ->assertDontSee('Someone Else Project');
});

it('shows create button when application is open', function (): void {
    $settings = app(ResearchProjectSettings::class);
    $settings->applicationOpen = true;
    $settings->save();

    Livewire::actingAs($this->user)
        ->test(Index::class)
        ->assertSee('Create Project');
});

it('hides create button when application is closed', function (): void {
    $settings = app(ResearchProjectSettings::class);
    $settings->applicationOpen = false;
    $settings->save();

    Livewire::actingAs($this->user)
        ->test(Index::class)
        ->assertDontSee('Create Project');
});

it('hides create button when user is already enrolled in a project', function (): void {
    $settings = app(ResearchProjectSettings::class);
    $settings->applicationOpen = true;
    $settings->save();

    $project = ResearchProject::factory()->published()->create();

    UserSelection::factory()->create([
        'user_id' => $this->user->id,
        'semester_id' => $this->semester->id,
        'elective_type' => ResearchProject::class,
        'elective_choice_id' => $project->id,
        'enrollment_type' => EnrollmentType::Direct,
        'status' => EnrollmentStatus::Confirmed,
    ]);

    Livewire::actingAs($this->user)
        ->test(Index::class)
        ->assertDontSee('Create Project');
});
