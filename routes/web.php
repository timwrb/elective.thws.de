<?php

use App\Livewire\Fwpm\FwpmIndex;
use App\Livewire\Fwpm\FwpmShow;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::livewire('fwpm/{semester}', FwpmIndex::class)->name('fwpm.index');
    Route::livewire('fwpm/{semester}/{fwpm}', FwpmShow::class)->name('fwpm.show');
});
