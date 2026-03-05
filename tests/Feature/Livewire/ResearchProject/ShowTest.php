<?php

use App\Enums\EnrollmentStatus;
use App\Enums\EnrollmentType;
use App\Livewire\ResearchProject\Show;
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
    $settings->maxStudentsPerProject = 5;
    $settings->save();
});

function enrollMember(User $user, ResearchProject $project, Semester $semester, EnrollmentStatus $status = EnrollmentStatus::Confirmed): UserSelection
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

it('redirects guests to login', function (): void {
    $project = ResearchProject::factory()->withInviteToken()->create();

    $this->get(route('research-projects.show', $project))
        ->assertRedirect(route('login'));
});

it('renders for an enrolled member', function (): void {
    $project = ResearchProject::factory()->withInviteToken()->create(['title' => 'Enrolled Project']);
    enrollMember($this->user, $project, $this->semester);

    Livewire::actingAs($this->user)
        ->test(Show::class, ['researchProject' => $project])
        ->assertOk()
        ->assertSee('Enrolled Project');
});

it('renders for the creator', function (): void {
    $project = ResearchProject::factory()->withCreator($this->user)->withInviteToken()->create(['title' => 'Creator Project']);
    enrollMember($this->user, $project, $this->semester);

    Livewire::actingAs($this->user)
        ->test(Show::class, ['researchProject' => $project])
        ->assertOk()
        ->assertSee('Creator Project');
});

it('returns 403 for non-members', function (): void {
    $project = ResearchProject::factory()->withInviteToken()->create();
    $outsider = User::factory()->create();

    $this->actingAs($outsider)
        ->get(route('research-projects.show', $project))
        ->assertForbidden();
});

it('shows invite link only to the creator', function (): void {
    $project = ResearchProject::factory()->withCreator($this->user)->withInviteToken()->create();
    enrollMember($this->user, $project, $this->semester);

    $member = User::factory()->create();
    enrollMember($member, $project, $this->semester);

    // Creator sees invite token in the page
    Livewire::actingAs($this->user)
        ->test(Show::class, ['researchProject' => $project])
        ->assertSee($project->invite_token);

    // Regular member does not see invite token
    Livewire::actingAs($member)
        ->test(Show::class, ['researchProject' => $project])
        ->assertDontSee($project->invite_token);
});

it('shows professor contact when assigned', function (): void {
    $professor = User::factory()->create(['name' => 'Dr', 'surname' => 'Müller', 'email' => 'mueller@thws.de']);
    $project = ResearchProject::factory()->withProfessor($professor)->withInviteToken()->create();
    enrollMember($this->user, $project, $this->semester);

    Livewire::actingAs($this->user)
        ->test(Show::class, ['researchProject' => $project])
        ->assertSee('Müller')
        ->assertSee('mueller@thws.de');
});

it('shows all enrolled members', function (): void {
    $project = ResearchProject::factory()->withInviteToken()->create();
    enrollMember($this->user, $project, $this->semester);

    $member2 = User::factory()->create(['surname' => 'Teammember']);
    enrollMember($member2, $project, $this->semester);

    Livewire::actingAs($this->user)
        ->test(Show::class, ['researchProject' => $project])
        ->assertSee('Teammember');
});

it('regenerates the invite token', function (): void {
    $project = ResearchProject::factory()->withCreator($this->user)->withInviteToken()->create();
    enrollMember($this->user, $project, $this->semester);

    $old = $project->invite_token;

    Livewire::actingAs($this->user)
        ->test(Show::class, ['researchProject' => $project])
        ->call('regenerateToken')
        ->assertDispatched('token-regenerated');

    expect($project->fresh()->invite_token)->not->toBe($old);
});

it('prevents non-creator from regenerating token', function (): void {
    $project = ResearchProject::factory()->withInviteToken()->create();
    enrollMember($this->user, $project, $this->semester);

    Livewire::actingAs($this->user)
        ->test(Show::class, ['researchProject' => $project])
        ->call('regenerateToken')
        ->assertForbidden();
});
