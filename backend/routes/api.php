<?php

// routes/api.php
use App\Http\Controllers\Api\V1\{FinanceApiController, JournalController, ProjectController, ToolboxController};
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Public (untuk Next.js). 'featured' harus sebelum '{slug}'
    Route::get('projects/featured', [ProjectController::class, 'featured']);
    Route::get('projects', [ProjectController::class, 'index']);
    Route::get('projects/{slug}', [ProjectController::class, 'show']);

    Route::get('journal', [JournalController::class, 'index']);
    Route::get('journal/{slug}', [JournalController::class, 'show']);

    Route::get('toolbox', [ToolboxController::class, 'index']);

    // Contact: ditunda, aktifkan saat controllernya dibuat
    // Route::post('contact', [ContactController::class, 'store'])->middleware('throttle:5,1');

    // Finance (Hermes) - jangan dihapus
    Route::prefix('finance')
        ->middleware(['auth:sanctum', 'active', 'module:finance', 'throttle:60,1'])
        ->group(function () {
            Route::get('categories', [FinanceApiController::class, 'categories']);
            Route::get('accounts', [FinanceApiController::class, 'accounts']);
            Route::get('summary', [FinanceApiController::class, 'summary'])->middleware('ability:finance:read');
            Route::get('transactions', [FinanceApiController::class, 'index'])->middleware('ability:finance:read');
            Route::post('transactions', [FinanceApiController::class, 'store'])->middleware('ability:finance:write');
            Route::delete('transactions/{id}', [FinanceApiController::class, 'destroy'])->middleware('ability:finance:write');
        });
});