<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AccessTokenController;
use App\Http\Controllers\Api\FeatureFlagController;
use Illuminate\Support\Facades\Route;

Route::prefix('/features')->group(function () {
    Route::post('/', [FeatureFlagController::class, 'index'])->name('index');
})->name('feature-flags');

Route::resource('/settings/tokens', AccessTokenController::class)->only(['store', 'show', 'destroy']);
