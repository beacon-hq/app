<?php

declare(strict_types=1);

use App\Events\FeatureFlagEvaluatedEvent;
use App\Http\Controllers\AccessTokenController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnvironmentController;
use App\Http\Controllers\FeatureFlagController;
use App\Http\Controllers\FeatureTypeController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\OurTeamController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\PrivacyPolicyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamMemberManageController;
use App\Http\Controllers\UserController;
use App\Http\TosController;
use App\Models\Application;
use App\Models\Environment;
use App\Values\FeatureFlag;
use App\Values\FeatureFlagContext;
use App\Values\FeatureFlagResponse;
use Bag\Values\Optional;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Lottery;

Route::get('/test', function () {
    $flag = FeatureFlag::from(\App\Models\FeatureFlag::inRandomOrder()->first());
    $c = FeatureFlagContext::from(
        scopeType: null,
        scope: [],
        appName: Application::inRandomOrder()->first()->name,
        environment: Environment::inRandomOrder()->first()->name,
        sessionId: Optional::empty(),
        ip: Optional::empty(),
        userAgent: Optional::empty(),
        referrer: Optional::empty(),
        url: Optional::empty(),
        method: Optional::empty()
    );
    $r = FeatureFlagResponse::from($flag->name, null, Lottery::odds(8, 10)->choose());

    FeatureFlagEvaluatedEvent::dispatch($flag, $c, $r);
})->name('test');

Route::get('/', [IndexController::class, 'index'])->name('welcome');
Route::post('/subscribe', [IndexController::class, 'store'])->name('subscribe');

Route::get('/privacy-policy', PrivacyPolicyController::class)->name('company.privacy-policy');
Route::get('/terms-of-service', TosController::class)->name('company.terms-of-service');
Route::get('/our-team', OurTeamController::class)->name('company.our-team');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/teams/accept-invite', [TeamMemberManageController::class, 'show'])
    ->middleware('signed')
    ->name('teams.accept-invite');

Route::middleware(['auth', 'auth:sanctum'])->group(function () {
    Route::resource('checkout', CheckoutController::class)
        ->only(['index', 'show', 'update']);
});

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

    Route::get('feature-flags/{featureFlag}/activity-log', [FeatureFlagController::class, 'activityLog'])
        ->name('feature-flags.activity-log');


    Route::resource(
        'feature-types',
        FeatureTypeController::class,
    )->except(['delete', 'create', 'show']);

    Route::patch('/feature-types/{feature_type}/set-default', [FeatureTypeController::class, 'setDefault'])
        ->name('feature-types.set-default');

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

        Route::resource('billing', BillingController::class)
            ->only(['index', 'show', 'destroy', 'update']);
    });

    Route::resource(
        'organizations',
        OrganizationController::class,
    )->except('create', 'show');

    Route::resource('users', UserController::class);

    Route::delete('invites/{invite}', [InviteController::class, 'destroy'])->name('invites.destroy');
    Route::post('invites/{invite}/resend', [InviteController::class, 'update'])->name('invites.resend');

    Route::prefix('feature-flags')->group(function () {
        Route::get('/feature-flags/{feature_flag}/overview', [FeatureFlagController::class, 'edit'])->name('feature-flags.edit.overview');
        Route::get('/feature-flags/{feature_flag}/policy', [FeatureFlagController::class, 'edit'])->name('feature-flags.edit.policy');
        Route::get('/feature-flags/{feature_flag}/activity', [FeatureFlagController::class, 'edit'])->name('feature-flags.edit.activity');
        Route::get('/feature-flags/{feature_flag}/metrics', [FeatureFlagController::class, 'edit'])->name('feature-flags.edit.metrics');
    });
});

require __DIR__.'/auth.php';
