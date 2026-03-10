<?php

use App\Enums\ElectiveStatus;
use App\Livewire\Awpf\Show;
use App\Models\Awpf;
use App\Models\Semester;
use App\Models\User;
use App\Settings\AwpfSettings;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->semester = Semester::factory()->create();
    $this->user = User::factory()->create();
});

it('redirects guests to login', function (): void {
    $awpf = Awpf::factory()->published()->create();

    $this->get(route('awpf.show', $awpf))
        ->assertRedirect(route('login'));
});

it('renders the course detail page for a published course', function (): void {
    $awpf = Awpf::factory()->published()->create(['name' => 'My Course', 'content' => 'Course content here']);

    Livewire::actingAs($this->user)
        ->test(Show::class, ['awpf' => $awpf])
        ->assertSee('My Course')
        ->assertSee('Course content here');
});

it('shows professor information when present', function (): void {
    $professor = User::factory()->create(['name' => 'Dr', 'surname' => 'Smith']);
    $awpf = Awpf::factory()->published()->withProfessor($professor)->create();

    Livewire::actingAs($this->user)
        ->test(Show::class, ['awpf' => $awpf])
        ->assertSee('Smith');
});

it('returns 404 for non-published courses', function (): void {
    $draft = Awpf::factory()->create(['status' => ElectiveStatus::Draft]);

    $this->actingAs($this->user)
        ->get(route('awpf.show', $draft))
        ->assertNotFound();
});

it('shows enrollment closed button when enrollment is closed', function (): void {
    $settings = app(AwpfSettings::class);
    $settings->enrollmentOpen = false;
    $settings->maxSelections = 5;
    $settings->minRequiredSelections = 1;
    $settings->save();

    $awpf = Awpf::factory()->published()->create();

    Livewire::actingAs($this->user)
        ->test(Show::class, ['awpf' => $awpf])
        ->assertSee('Enrollment is closed');
});

it('displays scheduled_at date in formatted schedule', function (): void {
    $awpf = Awpf::factory()->published()->create();
    $awpf->schedules()->create([
        'scheduled_at' => '2026-03-15 10:00:00',
        'duration_minutes' => 180,
    ]);

    Livewire::actingAs($this->user)
        ->test(Show::class, ['awpf' => $awpf])
        ->assertSee('15.03.2026')
        ->assertSee('10:00')
        ->assertSee('180');
});
