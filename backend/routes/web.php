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
        Route::view('/settings/api-keys', 'settings.api-keys')->name('settings.api-keys');
        Route::view('/accounts', 'finance.accounts')->name('accounts');
        Route::view('/categories', 'finance.categories')->name('categories');
    });

    Route::middleware('can:manage-content')->group(function () {
        // users, projects, journal
        Route::view('/admin/users', 'admin.users')->name('admin.users');
        Route::view('/admin/projects', 'admin.projects')->name('admin.projects');
        Route::view('/admin/journal', 'admin.journal')->name('admin.journal');

    });
});

require __DIR__ . '/auth.php';
