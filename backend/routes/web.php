<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::get('/', fn() => redirect()->route(auth()->check() ? 'dashboard' : 'login'));

Route::middleware(['guest', 'throttle:20,1'])->group(function () {
    Route::get('auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('auth/google/callback', [GoogleAuthController::class, 'callback']);
});

Route::post('/logout', function (Request $request) {
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

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
