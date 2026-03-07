<?php

use App\Livewire\Fwpm\FwpmIndex;
use App\Livewire\Fwpm\FwpmShow;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::prefix('awpf')->name('awpf.')->group(function () {
        Route::livewire('/', \App\Livewire\Awpf\Index::class)->name('index');
        Route::livewire('/select', \App\Livewire\Awpf\Select::class)->name('select');
        Route::livewire('/{awpf}', \App\Livewire\Awpf\Show::class)->name('show');
    });

    Route::livewire('fwpm/{semester}', FwpmIndex::class)->name('fwpm.index');
    Route::livewire('fwpm/{semester}/{fwpm}', FwpmShow::class)->name('fwpm.show');

    Route::prefix('research-projects')->name('research-projects.')->group(function () {
        Route::livewire('/', \App\Livewire\ResearchProject\Index::class)->name('index');
        Route::livewire('/create', \App\Livewire\ResearchProject\Create::class)->name('create');
        Route::livewire('/{researchProject}', \App\Livewire\ResearchProject\Show::class)->name('show');
        Route::livewire('/{researchProject}/edit', \App\Livewire\ResearchProject\Edit::class)->name('edit');
        Route::livewire('/{researchProject}/join/{token}', \App\Livewire\ResearchProject\Join::class)->name('join');
    });
});
