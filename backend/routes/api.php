<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\JournalController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ToolboxController;
use App\Http\Controllers\Api\UploadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Editorial Portfolio
|--------------------------------------------------------------------------
| Base URL: http://localhost:8000/api/v1
|
| Public routes:   no auth required
| Admin routes:    require Authorization: Bearer {token} (Sanctum)
*/

Route::prefix('v1')->group(function () {

    // ── Auth ──────────────────────────────────────────────────────────────
    // Route::post('auth/login',  [AuthController::class, 'login']);
    // Route::middleware('auth:sanctum')->group(function () {
    //     Route::post('auth/logout', [AuthController::class, 'logout']);
    //     Route::get('auth/me',      [AuthController::class, 'me']);
    // });

    // ── Projects (public) ─────────────────────────────────────────────────
    // NOTE: 'featured' must be registered BEFORE '{slug}' to avoid
    //       Laravel matching "featured" as a slug parameter.
    Route::get('projects/featured', [ProjectController::class, 'featured']);
    Route::get('projects',          [ProjectController::class, 'index']);
    Route::get('projects/{slug}',   [ProjectController::class, 'show']);

    // ── Journal (public) ──────────────────────────────────────────────────
    Route::get('journal',         [JournalController::class, 'index']);
    Route::get('journal/{slug}',  [JournalController::class, 'show']);

    // ── Toolbox (public) ──────────────────────────────────────────────────
    Route::get('toolbox', [ToolboxController::class, 'index']);

    // ── Contact (public, rate-limited) ────────────────────────────────────
    // throttle:5,1 = max 5 requests per 1 minute per IP
    Route::post('contact', [ContactController::class, 'store'])
         ->middleware('throttle:5,1');

    // ── Admin (all protected by Sanctum) ──────────────────────────────────
    Route::prefix('admin')->middleware('auth:sanctum')->group(function () {

        // Projects CRUD
        Route::post('projects',        [ProjectController::class, 'store']);
        Route::put('projects/{id}',    [ProjectController::class, 'update']);
        Route::delete('projects/{id}', [ProjectController::class, 'destroy']);

        // Journal CRUD
        Route::get('journal',          [JournalController::class, 'adminIndex']); // includes drafts
        Route::post('journal',         [JournalController::class, 'store']);
        Route::put('journal/{id}',     [JournalController::class, 'update']);
        Route::delete('journal/{id}',  [JournalController::class, 'destroy']);

        // File upload
        Route::post('uploads', [UploadController::class, 'store']);
    });
});