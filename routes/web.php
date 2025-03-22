<?php

declare(strict_types=1);

use App\Http\Controllers\AccessTokenController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\EnvironmentController;
use App\Http\Controllers\FeatureFlagController;
use App\Http\Controllers\FeatureTypeController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamMemberManageController;
use Illuminate\Foundation\Application as ApplicationAlias;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => ApplicationAlias::VERSION,
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

Route::get('/teams/accept-invite', [TeamMemberManageController::class, 'show'])
    ->middleware('signed')
    ->name('teams.accept-invite');

Route::middleware(['auth', 'verified', 'auth:sanctum'])->group(function () {
    Route::resource(
        'applications',
        ApplicationController::class,
        ['parameters' => ['applications' => 'slug']]
    )->except(['delete', 'create', 'show']);

    Route::resource(
        'environments',
        EnvironmentController::class,
        ['parameters' => ['environments' => 'slug']]
    )->except(['delete', 'create', 'show']);

    Route::resource(
        'feature-flags',
        FeatureFlagController::class,
        ['parameters' => ['feature-flags' => 'slug']]
    )->except(['delete', 'create', 'show']);

    Route::resource(
        'feature-types',
        FeatureTypeController::class,
        ['parameters' => ['feature-types' => 'slug']]
    )->except(['delete', 'create', 'show']);

    Route::resource(
        'tags',
        TagController::class,
        ['parameters' => ['tags' => 'slug']]
    )->except(['delete', 'create', 'show']);

    Route::resource(
        'settings',
        AccessTokenController::class,
        ['parameters' => ['settings' => 'slug']]
    )->only(['index']);

    Route::resource(
        'policies',
        PolicyController::class,
        ['parameters' => ['policies' => 'slug']]
    )->except(['delete', 'create', 'show']);

    Route::post(
        'team=members/invite/{slug}',
        [TeamMemberManageController::class, 'store'],
    )->name('team-members.invite');

    Route::delete(
        'teams-members/delete/{slug}',
        [TeamMemberManageController::class, 'destroy'],
    )->name('team-members.delete');

    Route::resource(
        'teams',
        TeamController::class,
        ['parameters' => ['teams' => 'slug']]
    )->except(['delete', 'create', 'show']);

    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingController::class, 'index'])
            ->name('settings.index');

        Route::get('/api', [AccessTokenController::class, 'index'])
            ->name('access-tokens.index');
    });

    Route::resource(
        'organizations',
        OrganizationController::class,
        ['parameters' => ['organizations' => 'id']]
    )->except('create', 'show');

    Route::prefix('policies')->group(function () {
        Route::get('/', [PolicyController::class, 'index'])
            ->name('policies.index');

        Route::get('/{slug}/edit', [PolicyController::class, 'edit'])
            ->name('policies.edit');

        Route::patch('/{slug}', [PolicyController::class, 'update'])
            ->name('policies.update');

        Route::put('/{slug}', [PolicyController::class, 'update'])
            ->name('policies.update');
    });

    Route::prefix('feature-flags')->group(function () {
        Route::get('/feature-flags/{slug}/overview', [FeatureFlagController::class, 'edit'])->name('feature-flags.edit.overview');
        Route::get('/feature-flags/{slug}/policy', [FeatureFlagController::class, 'edit'])->name('feature-flags.edit.policy');
    });
});

require __DIR__.'/auth.php';
