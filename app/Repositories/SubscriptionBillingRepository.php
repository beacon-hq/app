<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Organization;
use App\Models\StripeProduct;
use App\Values\Organization as OrganizationValue;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Checkout;

class SubscriptionBillingRepository
{
    public function __construct(protected ProductRepository $productRepository)
    {
    }

    public function hasActiveSubscription(OrganizationValue $organization): bool
    {
        $organization = Organization::findOrFail($organization->id);

        return $organization->subscriptions()->whereIn('stripe_status', ['active', 'trialing'])->exists();
    }

    public function isTrialSubscription(OrganizationValue $organization): bool
    {
        $organization = Organization::findOrFail($organization->id);

        return $organization->subscriptions()->where('stripe_status', 'trialing')->exists();
    }

    public function currentSubscription(OrganizationValue $organization): StripeProduct
    {
        $organization = Organization::findOrFail($organization->id);

        $subscription = $organization->subscription('plan');

        return StripeProduct::query()
            ->whereIn(
                'stripe_id',
                $subscription
                    ->items()
                    ->pluck('stripe_product')
            )
            ->whereNotNull('stripe_base_price_id')
            ->first();
    }

    public function createSubscription(OrganizationValue $organization, string $productId): Checkout
    {
        $organization = Organization::findOrFail($organization->id);
        $product = $this->productRepository->find($productId);

        $checkout = $organization->newSubscription('plan');

        return $checkout
            ->alwaysInvoice()
            ->price($product->basePrice->stripe_id)
            ->meteredPrice($product->meteredPrice->stripe_id)
            ->when(isset($product->entitlements['trial_length']), function ($checkout) use ($organization, $product) {
                $trialLength = new CarbonInterval($product->entitlements['trial_length']);

                $subscription = $organization->subscriptions()->whereNotNull('trial_ends_at')->latest()->first();
                if ($subscription !== null) {
                    if ($subscription->trial_ends_at->lessThan($subscription->updated_at)) {
                        return $checkout;
                    }
                    $trialLength = $subscription->updated_at->diff($subscription->trial_ends_at);
                }

                return $checkout->trialUntil(now()->add($trialLength)->endOfDay());
            })
            ->checkout([
                'success_url' => route('dashboard'),
                'cancel_url' => route('checkout.index'),
            ], [
                'description' => $product->name,
                'metadata' => [
                    'id' => $organization->id,
                ],
            ]);
    }

    public function changeSubscription(OrganizationValue $organization, string $productId): bool
    {
        try {
            $organization = Organization::findOrFail($organization->id);
            $product = $this->productRepository->find($productId);

            // Get the current subscription
            $subscription = $organization->subscription('plan');

            if (!$subscription) {
                return false;
            }

            // Swap to the new prices
            $subscription->endTrial()->swap([
                $product->basePrice->stripe_id,
                $product->meteredPrice->stripe_id,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to change subscription: ' . $e->getMessage(), [
                'exception' => $e,
                'organization_id' => $organization->id,
                'product_id' => $productId,
            ]);

            return false;
        }
    }
}
