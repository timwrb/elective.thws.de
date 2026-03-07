<?php

use App\Enums\ElectiveStatus;
use App\Models\Fwpm;
use App\Models\Semester;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $semester = Semester::factory()->winter()->year(2025)->create();
    $fwpm = Fwpm::factory()->create([
        'status' => ElectiveStatus::Published,
        'semester_id' => $semester->id,
    ]);

    $this->get(route('fwpm.show', [$semester, $fwpm]))
        ->assertRedirect(route('login'));
});

test('authenticated users can view the detail page', function () {
    $user = User::factory()->create();
    $semester = Semester::factory()->winter()->year(2025)->create();
    $fwpm = Fwpm::factory()->withProfessor()->create([
        'status' => ElectiveStatus::Published,
        'semester_id' => $semester->id,
    ]);

    $this->actingAs($user)
        ->get(route('fwpm.show', [$semester, $fwpm]))
        ->assertOk()
        ->assertSee($fwpm->name_german);
});

test('FWPM attributes are displayed', function () {
    $user = User::factory()->create();
    $semester = Semester::factory()->winter()->year(2025)->create();
    $fwpm = Fwpm::factory()->create([
        'status' => ElectiveStatus::Published,
        'semester_id' => $semester->id,
        'contents' => 'Test contents for this FWPM',
        'goals' => 'Test goals for this FWPM',
    ]);

    $this->actingAs($user)
        ->get(route('fwpm.show', [$semester, $fwpm]))
        ->assertOk()
        ->assertSee($fwpm->name_german)
        ->assertSee($fwpm->module_number)
        ->assertSee('Test contents for this FWPM')
        ->assertSee('Test goals for this FWPM')
        ->assertSee((string) $fwpm->credits);
});
