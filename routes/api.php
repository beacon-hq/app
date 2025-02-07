<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AccessTokenController;
use App\Http\Controllers\Api\FeatureFlagController;
use Illuminate\Support\Facades\Route;

Route::prefix('/features')->name('feature-flags.')->group(function () {
    Route::post('/', [FeatureFlagController::class, 'index'])->name('index');
    Route::post('/{slug}', [FeatureFlagController::class, 'show'])->name('show');
});

Route::resource('/settings/tokens', AccessTokenController::class)->except(['create', 'update', 'index', 'edit']);
