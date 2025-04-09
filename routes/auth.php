<?php

declare(strict_types=1);

use App\Features\BeaconEnabled;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\TwoFactorConfirmedController;
use App\Http\Controllers\Auth\TwoFactorSetupController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\OrganizationSelectController;
use App\Http\Controllers\TeamSelectController;
use Illuminate\Support\Facades\Route;
use Laravel\Pennant\Middleware\EnsureFeaturesAreActive;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->middleware(['guest', EnsureFeaturesAreActive::using(BeaconEnabled::class)])
        ->name('register');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::get('forgot-password', [NewPasswordController::class, 'create'])
        ->name('password.reset');
});

Route::middleware('auth')->prefix('user')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::middleware('password.confirm')->get('two-factor', [TwoFactorSetupController::class, 'index'])
        ->name('two-factor.setup');

    Route::get('two-factor-complete', [TwoFactorConfirmedController::class, 'show'])
        ->name('two-factor.confirmed');
});

Route::prefix('user')->get('two-factor-verification', [TwoFactorSetupController::class, 'show'])
    ->middleware(['guest:'.config('fortify.guard')])
    ->name('two-factor.login');

Route::middleware('auth')->prefix('/teams')->group(function () {
    Route::get('/select', [TeamSelectController::class, 'index'])
        ->name('teams.select');

    Route::post('/select', [TeamSelectController::class, 'update'])
        ->name('teams.choose');
});

Route::middleware('auth')->prefix('/organizations')->group(function () {
    Route::post('/select', [OrganizationSelectController::class, 'update'])
        ->name('organizations.choose');
});

Route::middleware('auth')->post('/email/verification-resend', [EmailVerificationNotificationController::class, 'update'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.resend');
