<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AccessTokenController;
use App\Http\Controllers\Api\FeatureFlagController;
use App\Http\Controllers\Api\OnboardingController;
use Illuminate\Support\Facades\Route;

Route::prefix('/features')->name('feature-flags.')->group(function () {
    Route::post('/', [FeatureFlagController::class, 'index'])->name('index');
    Route::post('/{feature_flag}', [FeatureFlagController::class, 'show'])->name('show');
});

Route::resource('access-tokens', AccessTokenController::class)->except(['create', 'update', 'index', 'edit'])->withoutMiddleware('throttle:api');

Route::get('/onboarding/status', [OnboardingController::class, 'status'])
    ->name('onboarding.status')
    ->withoutMiddleware('throttle:api');

Route::post('/onboarding/complete', [OnboardingController::class, 'complete'])
    ->name('onboarding.complete')
    ->withoutMiddleware('throttle:api');
