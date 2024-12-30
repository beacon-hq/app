<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\PersonalAccessToken;
use App\Models\Tenant;
use App\Values\AppContext;
use App\Values\Tenant as TenantValue;
use Illuminate\Support\Facades\App;
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

        App::macro('context', function (Tenant|TenantValue|null $tenant = null): AppContext {
            $context = resolve(AppContext::class);

            if ($tenant !== null) {
                if ($tenant instanceof Tenant) {
                    $tenant = TenantValue::from(id: $tenant->id, name: $tenant->name);
                }

                $context = $context->with(tenant: $tenant);
            }

            app()->singleton(AppContext::class, fn () => $context);

            return $context;
        });

        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
