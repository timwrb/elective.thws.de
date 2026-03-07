<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::prefix('awpf')->name('awpf.')->group(function () {
        Route::get('/', \App\Livewire\Awpf\Index::class)->name('index');
        Route::get('/select', \App\Livewire\Awpf\Select::class)->name('select');
        Route::get('/{awpf}', \App\Livewire\Awpf\Show::class)->name('show');
    });

    Route::prefix('research-projects')->name('research-projects.')->group(function () {
        Route::get('/', \App\Livewire\ResearchProject\Index::class)->name('index');
        Route::get('/create', \App\Livewire\ResearchProject\Create::class)->name('create');
        Route::get('/{researchProject}', \App\Livewire\ResearchProject\Show::class)->name('show');
        Route::get('/{researchProject}/edit', \App\Livewire\ResearchProject\Edit::class)->name('edit');
        Route::get('/{researchProject}/join/{token}', \App\Livewire\ResearchProject\Join::class)->name('join');
    });
});
