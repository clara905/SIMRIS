<?php

use App\Http\Controllers\Api\AssetCategoryController;
use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use Illuminate\Support\Facades\Route;

// ---- Publik ----
Route::get('captcha', [AuthController::class, 'captcha'])->middleware('throttle:30,1');
Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');

// ---- Wajib login & akun aktif ----
Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    // ---- Data: wajib sudah ditempatkan di unit kerja + punya permission ----
    Route::middleware('org')->group(function () {
        Route::get('dashboard/summary', [DashboardController::class, 'summary'])
            ->middleware('permission:report.view');
        Route::get('dashboard/staf', [DashboardController::class, 'staf'])
            ->middleware('permission:risk.assess');

        Route::middleware('permission:asset.view')->group(function () {
            Route::get('asset-categories', [AssetCategoryController::class, 'index']);
            Route::get('assets', [AssetController::class, 'index']);
        });

        Route::middleware('permission:asset.manage')->group(function () {
            Route::post('assets', [AssetController::class, 'store']);
            Route::put('assets/{asset}', [AssetController::class, 'update']);
            Route::delete('assets/{asset}', [AssetController::class, 'destroy']);
        });
    });
});