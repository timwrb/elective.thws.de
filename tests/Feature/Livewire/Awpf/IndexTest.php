<?php

use App\Enums\ElectiveStatus;
use App\Enums\Language;
use App\Livewire\Awpf\Index;
use App\Models\Awpf;
use App\Models\Semester;
use App\Models\User;
use App\Settings\AwpfSettings;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->semester = Semester::factory()->create();
});

it('redirects guests to login', function (): void {
    $this->get(route('awpf.index'))
        ->assertRedirect(route('login'));
});

it('renders the index page for authenticated users', function (): void {
    $this->actingAs(User::factory()->create())
        ->get(route('awpf.index'))
        ->assertOk();
});

it('shows only published courses for the current semester', function (): void {
    $user = User::factory()->create();

    $published = Awpf::factory()->published()->create(['name' => 'Published Course']);
    $published->assignToSemester($this->semester);

    $draft = Awpf::factory()->create(['name' => 'Draft Course', 'status' => ElectiveStatus::Draft]);
    $draft->assignToSemester($this->semester);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertSee('Published Course')
        ->assertDontSee('Draft Course');
});

it('filters courses by search term', function (): void {
    $user = User::factory()->create();

    $alpha = Awpf::factory()->published()->create(['name' => 'Alpha Course']);
    $alpha->assignToSemester($this->semester);

    $beta = Awpf::factory()->published()->create(['name' => 'Beta Course']);
    $beta->assignToSemester($this->semester);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('search', 'Alpha')
        ->assertSee('Alpha Course')
        ->assertDontSee('Beta Course');
});

it('filters courses by language', function (): void {
    $user = User::factory()->create();

    $english = Awpf::factory()->published()->create([
        'name' => 'English Course',
        'language' => Language::English,
    ]);
    $english->assignToSemester($this->semester);

    $german = Awpf::factory()->published()->create([
        'name' => 'German Course',
        'language' => Language::German,
    ]);
    $german->assignToSemester($this->semester);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('language', Language::English->value)
        ->assertSee('English Course')
        ->assertDontSee('German Course');
});

it('shows the enrollment open banner when enrollment is open', function (): void {
    $settings = app(AwpfSettings::class);
    $settings->enrollmentOpen = true;
    $settings->maxSelections = 5;
    $settings->minRequiredSelections = 1;
    $settings->save();

    Livewire::actingAs(User::factory()->create())
        ->test(Index::class)
        ->assertSee('Enrollment is open');
});
