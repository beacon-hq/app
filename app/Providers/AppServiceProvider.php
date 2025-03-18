<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\AccessToken;
use App\Models\Team;
use App\Values\AppContext;
use App\Values\Team as TeamValue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        app()->singleton(AppContext::class, fn () => AppContext::from(AppContext::empty()));

        App::macro('context', function (Team|TeamValue|null $team = null): AppContext {
            $context = resolve(AppContext::class);

            if ($team !== null) {
                if ($team instanceof Team) {
                    $team = TeamValue::from($team);
                }

                $context = $context->with(team: $team);
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
