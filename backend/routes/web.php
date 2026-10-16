<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::redirect('/', '/dashboard');

Route::middleware('auth')->get('/pending', fn() => view('pending'))->name('pending');

Route::middleware(['auth', 'active'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::view('/profile', 'profile')->name('profile');

    Route::middleware('module:finance')->prefix('finance')->name('finance.')->group(function () {
        // Step berikutnya: accounts, transactions, categories
    });

    Route::middleware('can:manage-content')->group(function () {
        // users, projects, journal
    });
});

require __DIR__ . '/auth.php';
