<?php

use App\Enums\EnrollmentStatus;
use App\Enums\EnrollmentType;
use App\Livewire\Awpf\Select;
use App\Models\Awpf;
use App\Models\Semester;
use App\Models\User;
use App\Models\UserSelection;
use App\Settings\AwpfSettings;
use Illuminate\Support\Facades\Date;
use Livewire\Livewire;

beforeEach(function (): void {
    Date::setTestNow('2026-03-15 12:00:00');

    // March 2026 → SemesterService resolves to year=2025, season=Winter
    $this->semester = Semester::factory()->winter()->year(2025)->create();
    $this->user = User::factory()->create();

    $settings = app(AwpfSettings::class);
    $settings->enrollmentOpen = true;
    $settings->maxSelections = 5;
    $settings->minRequiredSelections = 1;
    $settings->save();
});

it('redirects guests to login', function (): void {
    $this->get(route('awpf.select'))
        ->assertRedirect(route('login'));
});

it('renders the select page for authenticated users', function (): void {
    Livewire::actingAs($this->user)
        ->test(Select::class)
        ->assertOk();
});

it('shows available published courses', function (): void {
    $awpf = Awpf::factory()->published()->create(['name' => 'Elective One']);
    $awpf->assignToSemester($this->semester);

    Livewire::actingAs($this->user)
        ->test(Select::class)
        ->assertSee('Elective One');
});

it('can add a course to the ranked list', function (): void {
    $awpf = Awpf::factory()->published()->create();
    $awpf->assignToSemester($this->semester);

    Livewire::actingAs($this->user)
        ->test(Select::class)
        ->call('addCourse', $awpf->id)
        ->assertSet('rankedIds', [$awpf->id]);
});

it('cannot add duplicate courses', function (): void {
    $awpf = Awpf::factory()->published()->create();
    $awpf->assignToSemester($this->semester);

    Livewire::actingAs($this->user)
        ->test(Select::class)
        ->call('addCourse', $awpf->id)
        ->call('addCourse', $awpf->id)
        ->assertSet('rankedIds', [$awpf->id]);
});

it('can remove a course from the ranked list', function (): void {
    $awpf = Awpf::factory()->published()->create();
    $awpf->assignToSemester($this->semester);

    Livewire::actingAs($this->user)
        ->test(Select::class)
        ->call('addCourse', $awpf->id)
        ->call('removeCourse', $awpf->id)
        ->assertSet('rankedIds', []);
});

it('can reorder courses with moveUp and moveDown', function (): void {
    [$first, $second] = Awpf::factory()->published()->count(2)->create();
    $first->assignToSemester($this->semester);
    $second->assignToSemester($this->semester);

    $component = Livewire::actingAs($this->user)
        ->test(Select::class)
        ->call('addCourse', $first->id)
        ->call('addCourse', $second->id)
        ->assertSet('rankedIds', [$first->id, $second->id]);

    $component->call('moveDown', 0)
        ->assertSet('rankedIds', [$second->id, $first->id]);

    $component->call('moveUp', 1)
        ->assertSet('rankedIds', [$first->id, $second->id]);
});

it('persists selections as linked list UserSelection records', function (): void {
    [$first, $second] = Awpf::factory()->published()->count(2)->create();
    $first->assignToSemester($this->semester);
    $second->assignToSemester($this->semester);

    Livewire::actingAs($this->user)
        ->test(Select::class)
        ->call('addCourse', $first->id)
        ->call('addCourse', $second->id)
        ->call('confirmSave')
        ->call('saveSelections');

    $selections = UserSelection::query()
        ->forUser($this->user)
        ->forSemester($this->semester)
        ->awpf()
        ->get();

    expect($selections)->toHaveCount(2);

    $root = $selections->firstWhere('parent_elective_choice_id', null);
    expect($root->elective_choice_id)->toBe($first->id);
    expect($root->enrollment_type)->toBe(EnrollmentType::Priority);
    expect($root->status)->toBe(EnrollmentStatus::Pending);

    $child = $selections->firstWhere('parent_elective_choice_id', $root->id);
    expect($child->elective_choice_id)->toBe($second->id);
});

it('replaces previous selections on re-save', function (): void {
    $awpf = Awpf::factory()->published()->create();
    $awpf->assignToSemester($this->semester);

    // Save once
    Livewire::actingAs($this->user)
        ->test(Select::class)
        ->call('addCourse', $awpf->id)
        ->call('saveSelections');

    expect(UserSelection::query()->forUser($this->user)->awpf()->count())->toBe(1);

    // Re-save — count should still be 1 (no duplicates)
    Livewire::actingAs($this->user)
        ->test(Select::class)
        ->call('saveSelections');

    expect(UserSelection::query()->forUser($this->user)->awpf()->count())->toBe(1);
});

it('validates minimum required selections', function (): void {
    $settings = app(AwpfSettings::class);
    $settings->minRequiredSelections = 2;
    $settings->save();

    $awpf = Awpf::factory()->published()->create();
    $awpf->assignToSemester($this->semester);

    Livewire::actingAs($this->user)
        ->test(Select::class)
        ->call('addCourse', $awpf->id)
        ->call('confirmSave')
        ->assertHasErrors('rankedIds');
});

it('validates maximum allowed selections', function (): void {
    $settings = app(AwpfSettings::class);
    $settings->maxSelections = 1;
    $settings->save();

    [$first, $second] = Awpf::factory()->published()->count(2)->create();
    $first->assignToSemester($this->semester);
    $second->assignToSemester($this->semester);

    Livewire::actingAs($this->user)
        ->test(Select::class)
        ->call('addCourse', $first->id)
        ->call('addCourse', $second->id)
        ->assertHasErrors('rankedIds');
});

it('shows closed state when enrollment is not open', function (): void {
    $settings = app(AwpfSettings::class);
    $settings->enrollmentOpen = false;
    $settings->save();

    Livewire::actingAs($this->user)
        ->test(Select::class)
        ->assertSee('Enrollment is currently closed');
});

it('loads existing selections in correct order on mount', function (): void {
    [$first, $second] = Awpf::factory()->published()->count(2)->create();
    $first->assignToSemester($this->semester);
    $second->assignToSemester($this->semester);

    // Create the linked list manually in DB
    $rootSelection = UserSelection::query()->create([
        'user_id' => $this->user->id,
        'semester_id' => $this->semester->id,
        'elective_type' => Awpf::class,
        'elective_choice_id' => $first->id,
        'parent_elective_choice_id' => null,
        'status' => EnrollmentStatus::Pending,
        'enrollment_type' => EnrollmentType::Priority,
    ]);

    UserSelection::query()->create([
        'user_id' => $this->user->id,
        'semester_id' => $this->semester->id,
        'elective_type' => Awpf::class,
        'elective_choice_id' => $second->id,
        'parent_elective_choice_id' => $rootSelection->id,
        'status' => EnrollmentStatus::Pending,
        'enrollment_type' => EnrollmentType::Priority,
    ]);

    Livewire::actingAs($this->user)
        ->test(Select::class)
        ->assertSet('rankedIds', [$first->id, $second->id]);
});
