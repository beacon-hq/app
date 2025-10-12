<?php

declare(strict_types=1);

namespace App\Actions\Subscriptions;

use App\Actions\Traits\AsAction;
use App\Models\Organization as OrganizationModel;
use App\Models\StripeProduct;
use App\Values\Organization;
use App\Values\Product;
use Carbon\CarbonInterval;
use Laravel\Cashier\Checkout;

class CreateSubscription
{
    use AsAction;

    public function handle(Organization $organization, Product $product): Checkout
    {
        $organizationModel = OrganizationModel::findOrFail($organization->id);
        $productModel = StripeProduct::query()
            ->with(['basePrice', 'meteredPrice'])
            ->findOrFail($product->id);

        $checkout = $organizationModel->newSubscription('plan');

        return $checkout
            ->alwaysInvoice()
            ->price($productModel->basePrice->stripe_id)
            ->meteredPrice($productModel->meteredPrice->stripe_id)
            ->when(isset($productModel->entitlements['trial_length']), function ($checkout) use ($organizationModel, $productModel) {
                $trialLength = new CarbonInterval($productModel->entitlements['trial_length']);

                $subscription = $organizationModel->subscriptions()->whereNotNull('trial_ends_at')->latest()->first();
                if ($subscription !== null) {
                    if ($subscription->trial_ends_at->lessThan($subscription->updated_at)) {
                        return $checkout;
                    }
                    $trialLength = $subscription->updated_at->diff($subscription->trial_ends_at);
                }

                return $checkout->trialUntil(now()->add($trialLength)->endOfDay());
            })
            ->allowPromotionCodes()
            ->checkout([
                'success_url' => route('dashboard'),
                'cancel_url' => route('checkout.index'),
            ], [
                'description' => $productModel->name,
                'metadata' => [
                    'id' => $organizationModel->id,
                ],
            ]);
    }
}
