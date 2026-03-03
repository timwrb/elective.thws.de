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
});
