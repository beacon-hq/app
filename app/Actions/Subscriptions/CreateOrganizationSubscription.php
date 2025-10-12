<?php

declare(strict_types=1);

namespace App\Actions\Subscriptions;

use App\Actions\Products\FindProduct;
use App\Actions\Traits\AsAction;
use App\Models\Organization as OrganizationModel;
use App\Values\Organization;
use Carbon\CarbonInterval;
use Laravel\Cashier\Checkout;

class CreateOrganizationSubscription
{
    use AsAction;

    public function handle(Organization $organization, string $productId): Checkout
    {
        $organization = OrganizationModel::findOrFail($organization->id);
        $product = FindProduct::run($productId);

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
}
