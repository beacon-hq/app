<?php

declare(strict_types=1);

namespace App\Providers;

use App\Actions\Fortify\RejectInactive;
use App\Actions\Sanctum\GetAccessTokenClass;
use App\Http\Middleware\RequestTimingMiddleware;
use App\Models\Organization;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;
use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\CanonicalizeUsername;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Laravel\Nightwatch\Facades\Nightwatch;
use Laravel\Pennant\Middleware\EnsureFeaturesAreActive;
use Laravel\Sanctum\Sanctum;
use URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerNightwing();
        $this->registerMacros();
        $this->appSetup();

        app()->singleton(RequestTimingMiddleware::class);

        EnsureFeaturesAreActive::whenInactive(
            function () {
                abort(503);
            }
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        Cashier::useCustomerModel(Organization::class);
    }

    protected function registerNightwing(): void
    {
        Nightwatch::user(function (Authenticatable $user) {
            return [
                'name' => "{$user->first_name} {$user->last_name}",
                'username' => $user->email,
            ];
        });
    }

    protected function appSetup(): void
    {
        config('app.useTLS') ? URL::forceScheme('https') : URL::forceScheme('http');

        Date::use(CarbonImmutable::class);

        Sanctum::usePersonalAccessTokenModel(GetAccessTokenClass::run());

        Gate::guessPolicyNamesUsing(function (string $class) {
            return '\\App\\Policies\\' . class_basename($class) . 'Policy';
        });

        Fortify::authenticateThrough(function () {
            return array_filter([
                config('fortify.limiters.login') ? null : EnsureLoginIsNotThrottled::class,
                config('fortify.lowercase_usernames') ? CanonicalizeUsername::class : null,
                RejectInactive::class,
                Features::enabled(Features::twoFactorAuthentication()) ? RedirectIfTwoFactorAuthenticatable::class : null,
                AttemptToAuthenticate::class,
                PrepareAuthenticatedSession::class,
            ]);
        });
    }

    protected function registerMacros(): void
    {
        RedirectResponse::macro('withAlert', function (string $status, string $message) {
            return $this->with('alert', [
                'status' => $status,
                'message' => $message,
                'timestamp' => now()->getPreciseTimestamp(),
            ]);
        });

        Builder::macro('existsOrFail', function () {
            return $this->existsOr(fn () => throw (new ModelNotFoundException())->setModel($this->model::class));
        });
    }
}
