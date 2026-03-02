<?php

use App\Enums\ElectiveStatus;
use App\Livewire\Fwpm\FwpmIndex;
use App\Models\Fwpm;
use App\Models\Semester;
use App\Models\User;
use App\Models\UserSelection;
use Livewire\Livewire;

test('guests are redirected to the login page', function () {
    $semester = Semester::factory()->winter()->year(2025)->create();

    $this->get(route('fwpm.index', $semester))
        ->assertRedirect(route('login'));
});

test('authenticated users can visit the selection page', function () {
    $user = User::factory()->create();
    $semester = Semester::factory()->winter()->year(2025)->create();

    $this->actingAs($user)
        ->get(route('fwpm.index', $semester))
        ->assertOk();
});

test('available FWPMs are shown for semester', function () {
    $user = User::factory()->create();
    $semester = Semester::factory()->winter()->year(2025)->create();

    $fwpm = Fwpm::factory()->create([
        'status' => ElectiveStatus::Published,
        'semester_id' => $semester->id,
    ]);

    $this->actingAs($user)
        ->get(route('fwpm.index', $semester))
        ->assertOk()
        ->assertSee($fwpm->name_german);
});

test('empty state when no FWPMs', function () {
    $user = User::factory()->create();
    $semester = Semester::factory()->winter()->year(2025)->create();

    $this->actingAs($user)
        ->get(route('fwpm.index', $semester))
        ->assertOk()
        ->assertSee(__('No FWPMs available for this semester.'));
});

test('valid selections can be saved', function () {
    $user = User::factory()->create();
    $semester = Semester::factory()->winter()->year(2025)->create();

    $fwpms = Fwpm::factory()
        ->count(3)
        ->sequence(
            ['status' => ElectiveStatus::Published, 'semester_id' => $semester->id],
            ['status' => ElectiveStatus::Published, 'semester_id' => $semester->id],
            ['status' => ElectiveStatus::Published, 'semester_id' => $semester->id],
        )
        ->create();

    $orderedIds = $fwpms->pluck('id')->all();

    Livewire::actingAs($user)
        ->test(FwpmIndex::class, ['semester' => $semester])
        ->call('saveSelections', $orderedIds, 1)
        ->assertDispatched('selections-saved');

    expect(UserSelection::query()->forUser($user)->forSemester($semester)->fwpm()->count())->toBe(3);
});

test('linked-list order is correct in DB', function () {
    $user = User::factory()->create();
    $semester = Semester::factory()->winter()->year(2025)->create();

    $fwpms = Fwpm::factory()
        ->count(3)
        ->sequence(
            ['status' => ElectiveStatus::Published, 'semester_id' => $semester->id],
            ['status' => ElectiveStatus::Published, 'semester_id' => $semester->id],
            ['status' => ElectiveStatus::Published, 'semester_id' => $semester->id],
        )
        ->create();

    $orderedIds = $fwpms->pluck('id')->all();

    Livewire::actingAs($user)
        ->test(FwpmIndex::class, ['semester' => $semester])
        ->call('saveSelections', $orderedIds, 1);

    $selections = UserSelection::query()->forUser($user)->forSemester($semester)->fwpm()->get();
    $root = $selections->firstWhere('parent_elective_choice_id', null);

    expect($root->elective_choice_id)->toBe($orderedIds[0]);

    $second = $selections->firstWhere('parent_elective_choice_id', $root->id);
    expect($second->elective_choice_id)->toBe($orderedIds[1]);

    $third = $selections->firstWhere('parent_elective_choice_id', $second->id);
    expect($third->elective_choice_id)->toBe($orderedIds[2]);
});

test('minimum choice count is enforced', function () {
    $user = User::factory()->create();
    $semester = Semester::factory()->winter()->year(2025)->create();

    Livewire::actingAs($user)
        ->test(FwpmIndex::class, ['semester' => $semester])
        ->call('saveSelections', [], 1)
        ->assertNotDispatched('selections-saved');

    expect(UserSelection::query()->forUser($user)->forSemester($semester)->fwpm()->count())->toBe(0);
});

test('maximum choice count is enforced', function () {
    $user = User::factory()->create();
    $semester = Semester::factory()->winter()->year(2025)->create();

    $fwpms = Fwpm::factory()
        ->count(5)
        ->sequence(
            ['status' => ElectiveStatus::Published, 'semester_id' => $semester->id],
            ['status' => ElectiveStatus::Published, 'semester_id' => $semester->id],
            ['status' => ElectiveStatus::Published, 'semester_id' => $semester->id],
            ['status' => ElectiveStatus::Published, 'semester_id' => $semester->id],
            ['status' => ElectiveStatus::Published, 'semester_id' => $semester->id],
        )
        ->create();

    Livewire::actingAs($user)
        ->test(FwpmIndex::class, ['semester' => $semester])
        ->call('saveSelections', $fwpms->pluck('id')->all(), 1)
        ->assertNotDispatched('selections-saved');

    expect(UserSelection::query()->forUser($user)->forSemester($semester)->fwpm()->count())->toBe(0);
});

test('invalid FWPM IDs are rejected', function () {
    $user = User::factory()->create();
    $semester = Semester::factory()->winter()->year(2025)->create();

    Livewire::actingAs($user)
        ->test(FwpmIndex::class, ['semester' => $semester])
        ->call('saveSelections', ['non-existent-id'], 1)
        ->assertNotDispatched('selections-saved');

    expect(UserSelection::query()->forUser($user)->forSemester($semester)->fwpm()->count())->toBe(0);
});

test('duplicate IDs are rejected', function () {
    $user = User::factory()->create();
    $semester = Semester::factory()->winter()->year(2025)->create();

    $fwpm = Fwpm::factory()->create([
        'status' => ElectiveStatus::Published,
        'semester_id' => $semester->id,
    ]);

    Livewire::actingAs($user)
        ->test(FwpmIndex::class, ['semester' => $semester])
        ->call('saveSelections', [$fwpm->id, $fwpm->id], 1)
        ->assertNotDispatched('selections-saved');

    expect(UserSelection::query()->forUser($user)->forSemester($semester)->fwpm()->count())->toBe(0);
});

test('saving replaces previous selections', function () {
    $user = User::factory()->create();
    $semester = Semester::factory()->winter()->year(2025)->create();

    $fwpms = Fwpm::factory()
        ->count(3)
        ->sequence(
            ['status' => ElectiveStatus::Published, 'semester_id' => $semester->id],
            ['status' => ElectiveStatus::Published, 'semester_id' => $semester->id],
            ['status' => ElectiveStatus::Published, 'semester_id' => $semester->id],
        )
        ->create();

    $component = Livewire::actingAs($user)
        ->test(FwpmIndex::class, ['semester' => $semester]);

    $component->call('saveSelections', $fwpms->pluck('id')->all(), 1);
    expect(UserSelection::query()->forUser($user)->forSemester($semester)->fwpm()->count())->toBe(3);

    $component->call('saveSelections', [$fwpms[0]->id], 1);
    expect(UserSelection::query()->forUser($user)->forSemester($semester)->fwpm()->count())->toBe(1);
});
