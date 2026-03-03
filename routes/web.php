<?php

use App\Livewire\Awpf\Index;
use App\Livewire\Awpf\Select;
use App\Livewire\Awpf\Show;
use App\Livewire\Fwpm\FwpmIndex;
use App\Livewire\Fwpm\FwpmShow;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::prefix('fwpm')->name('fwpm.')->group(function () {
        Route::livewire('/{semester}', FwpmIndex::class)->name('fwpm.index');
        Route::livewire('{semester}/{fwpm}', FwpmShow::class)->name('fwpm.show');
    });

    Route::prefix('awpf')->name('awpf.')->group(function () {
        Route::livewire('/', Index::class)->name('index');
        Route::livewire('/select', Select::class)->name('select');
        Route::livewire('/{awpf}', Show::class)->name('show');
    });
});
