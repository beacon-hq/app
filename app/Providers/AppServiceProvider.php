<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\OrganizationChangedEvent;
use App\Events\TeamChangedEvent;
use App\Models\AccessToken;
use App\Models\Organization;
use App\Models\Team;
use App\Values\AppContext;
use App\Values\Organization as OrganizationValue;
use App\Values\Team as TeamValue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;
use Laravel\Pennant\Middleware\EnsureFeaturesAreActive;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // URL::forceScheme('https');

        app()->singleton(AppContext::class, fn () => AppContext::from(AppContext::empty()));

        App::macro('context', function (Organization|OrganizationValue|null $organization = null, Team|TeamValue|null $team = null): AppContext {
            $context = resolve(AppContext::class);

            if ($organization !== null) {
                if ($organization instanceof Organization) {
                    $organization = OrganizationValue::from($organization);
                }

                if (!$context->has('organization') || $context->organization->id !== $organization->id) {
                    $context = $context->with(organization: $organization);
                    OrganizationChangedEvent::dispatch($organization);
                }
            }

            if ($team !== null) {
                if ($team instanceof Team) {
                    $team = TeamValue::from($team);
                }

                if (!$context->has('team') || $context->team->id !== $team->id) {
                    $context = $context->with(team: $team);
                    TeamChangedEvent::dispatch($team);
                }
            }

            app()->singleton(AppContext::class, fn () => $context);

            return $context;
        });

        Sanctum::usePersonalAccessTokenModel(AccessToken::class);

        Gate::guessPolicyNamesUsing(function (string $class) {
            return '\\App\\Policies\\' . class_basename($class) . 'Policy';
        });

        RedirectResponse::macro('withAlert', function (string $status, string $message) {
            return $this->with('alert', [
                'status' => $status,
                'message' => $message,
            ]);
        });

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
}
