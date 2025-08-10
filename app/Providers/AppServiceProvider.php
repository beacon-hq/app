<?php

declare(strict_types=1);

namespace App\Providers;

use App\Actions\Fortify\RejectInactive;
use App\Events\OrganizationChangedEvent;
use App\Events\TeamChangedEvent;
use App\Http\Middleware\RequestTimingMiddleware;
use App\Models\AccessToken;
use App\Models\Organization;
use App\Models\Team;
use App\Values\AppContext;
use App\Values\Organization as OrganizationValue;
use App\Values\Team as TeamValue;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;
use Laravel\Dusk\Dusk;
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
        app()->singleton(AppContext::class, fn () => AppContext::from(AppContext::empty()));

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

        Sanctum::usePersonalAccessTokenModel(AccessToken::class);

        Gate::guessPolicyNamesUsing(function (string $class) {
            return '\\App\\Policies\\' . class_basename($class) . 'Policy';
        });

        if (app()->environment('local')) {
            Dusk::selectorHtmlAttribute('data-dusk');
        }

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

        App::macro('context', function (Organization|OrganizationValue|null $organization = null, Team|TeamValue|null $team = null): AppContext {
            $newContext = $context = resolve(AppContext::class);

            if ($organization !== null) {
                if ($organization instanceof Organization) {
                    $organization = OrganizationValue::from($organization);
                }

                if (!$context->has('organization') || $context->organization->id !== $organization->id) {
                    $newContext = $newContext->with(organization: $organization);
                }
            }

            if ($team !== null) {
                if ($team instanceof Team) {
                    $team = TeamValue::from($team);
                }

                if (!$context->has('team') || $context->team->id !== $team->id) {
                    $newContext = $newContext->with(team: $team);
                }
            }

            app()->singleton(AppContext::class, fn () => $newContext);

            if ($newContext->has('organization') && (!$context->has('organization') || $newContext->organization->id !== $context->organization->id)) {
                OrganizationChangedEvent::dispatch($organization);
            }

            if ($newContext->has('team') && (!$context->has('team') || $newContext->team->id !== $context->team->id)) {
                TeamChangedEvent::dispatch($team);
            }

            return $newContext;
        });
    }
}
