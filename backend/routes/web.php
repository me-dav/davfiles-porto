<?php

use App\Http\Controllers\dashboard\AuthController;
use App\Http\Controllers\dashboard\ProjectController;
use App\Http\Controllers\dashboard\JournalController;
use App\Http\Controllers\dashboard\UploadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Dashboard (Blade + Session Auth)
|--------------------------------------------------------------------------
| Semua route dashboard dilindungi session auth Laravel.
| Akses: http://localhost:8000/dashboard
*/

// ── Auth ──────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// ── Dashboard (semua protected session auth) ──────────────────────────────
Route::prefix('/')
    ->middleware('auth')
    ->name('dashboard.')
    ->group(function () {

    // Home → redirect ke projects
    Route::get('/', fn() => redirect()->route('dashboard.projects.index'));

    // Projects CRUD
    Route::resource('projects', ProjectController::class);

    // Journal CRUD
    Route::resource('journal', JournalController::class);

    // Upload (image upload dari form)
    Route::post('/upload', [UploadController::class, 'store'])->name('upload');
});