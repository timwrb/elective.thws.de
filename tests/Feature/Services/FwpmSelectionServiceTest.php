<?php

use App\Enums\ElectiveStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\EnrollmentType;
use App\Models\Fwpm;
use App\Models\Semester;
use App\Models\User;
use App\Models\UserSelection;
use App\Services\FwpmSelectionService;

test('getChoiceBounds returns correct min and max', function () {
    $service = new FwpmSelectionService;

    expect($service->getChoiceBounds(3))->toBe(['min' => 3, 'max' => 5]);
    expect($service->getChoiceBounds(1))->toBe(['min' => 1, 'max' => 3]);
});

test('getDesiredCountRange returns min 1 max 5', function () {
    $service = new FwpmSelectionService;

    expect($service->getDesiredCountRange())->toBe(['min' => 1, 'max' => 5]);
});

test('saveSelections creates linked list in correct order', function () {
    $service = new FwpmSelectionService;
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

    $service->saveSelections($user, $semester, $orderedIds, 1);

    $selections = UserSelection::query()
        ->forUser($user)
        ->forSemester($semester)
        ->fwpm()
        ->get();

    expect($selections)->toHaveCount(3);

    $root = $selections->firstWhere('parent_elective_choice_id', null);
    expect($root)->not->toBeNull();
    expect($root->elective_choice_id)->toBe($orderedIds[0]);
    expect($root->status)->toBe(EnrollmentStatus::Pending);
    expect($root->enrollment_type)->toBe(EnrollmentType::Priority);

    $second = $selections->firstWhere('parent_elective_choice_id', $root->id);
    expect($second->elective_choice_id)->toBe($orderedIds[1]);

    $third = $selections->firstWhere('parent_elective_choice_id', $second->id);
    expect($third->elective_choice_id)->toBe($orderedIds[2]);
});

test('saveSelections deletes existing selections first', function () {
    $service = new FwpmSelectionService;
    $user = User::factory()->create();
    $semester = Semester::factory()->winter()->year(2025)->create();

    $fwpms = Fwpm::factory()
        ->count(2)
        ->sequence(
            ['status' => ElectiveStatus::Published, 'semester_id' => $semester->id],
            ['status' => ElectiveStatus::Published, 'semester_id' => $semester->id],
        )
        ->create();

    $orderedIds = $fwpms->pluck('id')->all();

    $service->saveSelections($user, $semester, $orderedIds, 1);
    expect(UserSelection::query()->forUser($user)->forSemester($semester)->fwpm()->count())->toBe(2);

    $service->saveSelections($user, $semester, [$orderedIds[1]], 1);
    expect(UserSelection::query()->forUser($user)->forSemester($semester)->fwpm()->count())->toBe(1);

    $remaining = UserSelection::query()->forUser($user)->forSemester($semester)->fwpm()->first();
    expect($remaining->elective_choice_id)->toBe($orderedIds[1]);
});

test('loadSelections returns ordered FWPM IDs', function () {
    $service = new FwpmSelectionService;
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

    $service->saveSelections($user, $semester, $orderedIds, 1);

    $loaded = $service->loadSelections($user, $semester);

    expect($loaded)->toBe($orderedIds);
});

test('loadSelections handles empty state', function () {
    $service = new FwpmSelectionService;
    $user = User::factory()->create();
    $semester = Semester::factory()->winter()->year(2025)->create();

    expect($service->loadSelections($user, $semester))->toBe([]);
});

test('saveSelections rejects invalid FWPM IDs', function () {
    $service = new FwpmSelectionService;
    $user = User::factory()->create();
    $semester = Semester::factory()->winter()->year(2025)->create();

    $service->saveSelections($user, $semester, ['non-existent-id'], 1);
})->throws(\InvalidArgumentException::class);

test('saveSelections rejects duplicate FWPM IDs', function () {
    $service = new FwpmSelectionService;
    $user = User::factory()->create();
    $semester = Semester::factory()->winter()->year(2025)->create();

    $fwpm = Fwpm::factory()->create([
        'status' => ElectiveStatus::Published,
        'semester_id' => $semester->id,
    ]);

    $service->saveSelections($user, $semester, [$fwpm->id, $fwpm->id], 1);
})->throws(\InvalidArgumentException::class);

test('loadSelectionsWithStatus returns ordered models with elective', function () {
    $service = new FwpmSelectionService;
    $user = User::factory()->create();
    $semester = Semester::factory()->winter()->year(2025)->create();

    $fwpms = Fwpm::factory()
        ->count(2)
        ->sequence(
            ['status' => ElectiveStatus::Published, 'semester_id' => $semester->id],
            ['status' => ElectiveStatus::Published, 'semester_id' => $semester->id],
        )
        ->create();

    $orderedIds = $fwpms->pluck('id')->all();
    $service->saveSelections($user, $semester, $orderedIds, 1);

    $result = $service->loadSelectionsWithStatus($user, $semester);

    expect($result)->toHaveCount(2);
    expect($result->first()->elective_choice_id)->toBe($orderedIds[0]);
    expect($result->first()->elective)->toBeInstanceOf(Fwpm::class);
    expect($result->last()->elective_choice_id)->toBe($orderedIds[1]);
});
