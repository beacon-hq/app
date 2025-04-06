<?php

declare(strict_types=1);

use App\Http\Controllers\AccessTokenController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\EnvironmentController;
use App\Http\Controllers\FeatureFlagController;
use App\Http\Controllers\FeatureTypeController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamMemberManageController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [IndexController::class, 'index'])->name('welcome');
Route::post('/subscribe', [IndexController::class, 'store'])->name('subscribe');

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
    )->except(['delete', 'create', 'show']);

    Route::resource(
        'environments',
        EnvironmentController::class,
    )->except(['delete', 'create', 'show']);

    Route::resource(
        'feature-flags',
        FeatureFlagController::class,
    )->except(['delete', 'create', 'show']);

    Route::resource(
        'feature-types',
        FeatureTypeController::class,
    )->except(['delete', 'create', 'show']);

    Route::resource(
        'tags',
        TagController::class,
    )->except(['delete', 'create', 'show']);

    Route::resource(
        'settings',
        AccessTokenController::class,
    )->only(['index']);

    Route::resource(
        'policies',
        PolicyController::class,
    )->except(['delete', 'create', 'show']);

    Route::post(
        'team-members/invite/{team}',
        [TeamMemberManageController::class, 'store'],
    )->name('team-members.invite');

    Route::delete(
        'teams-members/delete/{team}',
        [TeamMemberManageController::class, 'destroy'],
    )->name('team-members.delete');

    Route::resource(
        'teams',
        TeamController::class,
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
    )->except('create', 'show');

    Route::resource('users', UserController::class);

    Route::prefix('policies')->group(function () {
        Route::get('/', [PolicyController::class, 'index'])
            ->name('policies.index');

        Route::get('/{policy}/edit', [PolicyController::class, 'edit'])
            ->name('policies.edit');

        Route::patch('/{policy}', [PolicyController::class, 'update'])
            ->name('policies.update');

        Route::put('/{policy}', [PolicyController::class, 'update'])
            ->name('policies.update');
    });

    Route::prefix('feature-flags')->group(function () {
        Route::get('/feature-flags/{feature_flag}/overview', [FeatureFlagController::class, 'edit'])->name('feature-flags.edit.overview');
        Route::get('/feature-flags/{feature_flag}/policy', [FeatureFlagController::class, 'edit'])->name('feature-flags.edit.policy');
    });
});

require __DIR__.'/auth.php';
