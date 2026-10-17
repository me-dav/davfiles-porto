<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

// Route::view('dashboard', 'dashboard')
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');

// Route::view('profile', 'profile')
//     ->middleware(['auth'])
//     ->name('profile');

Route::redirect('/', '/dashboard');

Route::middleware('auth')->get('/pending', fn() => view('pending'))->name('pending');

Route::middleware(['auth', 'active'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::view('/profile', 'profile')->name('profile');

    Route::middleware('module:finance')->prefix('finance')->name('finance.')->group(function () {
        // routes/web.php, di dalam grup module:finance
        Route::view('/transactions', 'finance.transactions')->name('transactions');
    });

    Route::middleware('can:manage-content')->group(function () {
        // users, projects, journal
    });
});

require __DIR__ . '/auth.php';
