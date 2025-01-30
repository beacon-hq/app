<?php

declare(strict_types=1);

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\EnvironmentController;
use App\Http\Controllers\FeatureFlagController;
use App\Http\Controllers\FeatureTypeController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TagController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'auth:sanctum'])->group(function () {
    Route::resource('applications', ApplicationController::class, ['parameters' => ['applications' => 'slug']])->except(['delete', 'create']);
    Route::resource('environments', EnvironmentController::class, ['parameters' => ['environments' => 'slug']])->except(['delete', 'create']);
    Route::resource('feature-flags', FeatureFlagController::class, ['parameters' => ['feature-flags' => 'slug']])->except(['delete', 'create']);
    Route::resource('feature-types', FeatureTypeController::class, ['parameters' => ['feature-types' => 'slug']])->except(['delete', 'create']);
    Route::resource('tags', TagController::class, ['parameters' => ['tags' => 'slug']])->except(['delete', 'create']);
    Route::resource('settings', SettingsController::class, ['parameters' => ['settings' => 'slug']])->only(['index']);
    Route::resource('policies', PolicyController::class, ['parameters' => ['policies' => 'slug']])->except(['delete', 'create']);

    Route::get('/feature-flags/{slug}/overview', [FeatureFlagController::class, 'edit'])->name('feature-flags.edit.overview');
    Route::get('/feature-flags/{slug}/policy', [FeatureFlagController::class, 'edit'])->name('feature-flags.edit.policy');
});

require __DIR__.'/auth.php';
