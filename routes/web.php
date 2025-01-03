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
    Route::resource('applications', ApplicationController::class)->except(['delete', 'create']);
    Route::resource('environments', EnvironmentController::class)->except(['delete', 'create']);
    Route::resource('feature-flags', FeatureFlagController::class)->except(['delete', 'create']);
    Route::resource('feature-types', FeatureTypeController::class)->except(['delete', 'create']);
    Route::resource('tags', TagController::class)->except(['delete', 'create']);
    Route::resource('settings', SettingsController::class)->only(['index']);
    Route::resource('policies', PolicyController::class)->except(['delete', 'create']);
});

require __DIR__.'/auth.php';
