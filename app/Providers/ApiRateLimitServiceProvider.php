<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Organization;
use App\Models\StripeProduct;
use App\Services\SubscriptionBillingService;
use App\Values\Organization as OrganizationValue;
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
        if (!config('beacon.billing.enabled') || !config('beacon.api.rate_limiting.enabled', false)) {
            RateLimiter::for('api', function (Request $request) {
                return Limit::none();
            });

            return;
        }

        // Define rate limiter for API requests
        RateLimiter::for('api', function (Request $request) {
            $organization = App::context()->has('organization') ? App::context()->organization : OrganizationValue::from(auth()->user()?->organization ?? []);

            // Get the organization service from the container
            $subscriptionBillingService = $this->app->make(SubscriptionBillingService::class);

            // Check if the organization has an active subscription
            $hasActiveSubscription = $subscriptionBillingService->hasActiveSubscription($organization);

            // Get the organization model to check subscription status
            $organizationModel = Organization::findOrFail($organization->id);

            // Check if the organization is on a trial subscription
            $isTrialSubscription = $organizationModel->subscriptions()
                ->where('stripe_status', 'trialing')
                ->exists();

            // Define rate limits based on subscription status
            if (!$hasActiveSubscription || $isTrialSubscription) {
                return Limit::perSecond(5)->by($organization->id);
            } else {
                // Paid subscription - get the subscription to determine tier
                $subscription = $organizationModel
                    ->subscriptions()
                    ->where('stripe_status', 'active')
                    ->first();


                if ($subscription) {
                    // Get the product to determine the tier
                    $product = StripeProduct::query()
                        ->whereIn(
                            'stripe_id',
                            $subscription
                                ->items()
                                ->pluck('stripe_product')
                        )
                        ->whereNotNull('stripe_base_price_id')
                        ->first();

                    if ($product) {
                        $evaluations = $product->entitlements['evaluations'] ?? null;

                        return match (true) {
                            $evaluations >= 2000000 => Limit::perSecond(100)->by($organization->id), // Enterprise
                            $evaluations >= 500000 => Limit::perSecond(50)->by($organization->id), // Scale
                            $evaluations >= 100000 => Limit::perSecond(20)->by($organization->id), // Growth
                            default => Limit::perSecond(10)->by($organization->id),
                        };
                    }
                }

                // Default to Solo post-trial if no product is found
                return Limit::perSecond(10)->by($organization->id);
            }
        });
    }
}
