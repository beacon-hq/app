<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Organization;
use App\Services\OrganizationService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class ApiRateLimitServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Skip rate limiting if it's disabled
        if (!config('beacon.api.rate_limiting.enabled', true)) {
            RateLimiter::for('api', function (Request $request) {
                return Limit::none();
            });

            return;
        }

        // Define rate limiter for API requests
        RateLimiter::for('api', function (Request $request) {
            $organization = App::context()->organization;

            // Get the organization service from the container
            $organizationService = $this->app->make(OrganizationService::class);

            // Check if the organization has an active subscription
            $hasActiveSubscription = $organizationService->hasActiveSubscription($organization);

            // Get the organization model to check subscription status
            $organizationModel = Organization::findOrFail($organization->id);

            // Check if the organization is on a trial subscription
            $isTrialSubscription = $organizationModel->subscriptions()
                ->where('stripe_status', 'trialing')
                ->exists();

            // Define rate limits based on subscription status
            if (!$hasActiveSubscription) {
                // No active subscription - very limited access
                return Limit::perSecond(5)->by($organization->id);
            } elseif ($isTrialSubscription) {
                // Solo during trial: 5/sec
                return Limit::perSecond(5)->by($organization->id);
            } else {
                // Paid subscription - get the subscription to determine tier
                $subscription = $organizationModel->subscriptions()
                    ->where('stripe_status', 'active')
                    ->first();

                if ($subscription) {
                    // Get the product to determine the tier
                    $product = $subscription->items->first()?->product;

                    if ($product) {
                        // Determine the tier based on the product name
                        $productName = strtolower($product->name);

                        if (str_contains($productName, 'scale')) {
                            // Scale tier: 100/sec
                            return Limit::perSecond(100)->by($organization->id);
                        } elseif (str_contains($productName, 'growth')) {
                            // Growth tier: 50/sec
                            return Limit::perSecond(50)->by($organization->id);
                        } elseif (str_contains($productName, 'team')) {
                            // Team tier: 20/sec
                            return Limit::perSecond(20)->by($organization->id);
                        } else {
                            // Solo post-trial: 10/sec (default for any other product)
                            return Limit::perSecond(10)->by($organization->id);
                        }
                    }
                }

                // Default to Solo post-trial if no product is found
                return Limit::perSecond(10)->by($organization->id);
            }
        });
    }
}
