<?php

declare(strict_types=1);

namespace App\Actions\Subscriptions;

use App\Actions\Traits\AsAction;
use App\Features\BillingEnabled;
use App\Models\Organization;
use App\Models\StripeProduct;
use App\Values\Organization as OrganizationValue;
use App\Values\Product;
use Laravel\Pennant\Feature;

class GetSubscriptionPlan
{
    use AsAction;

    public function handle(OrganizationValue $organization): ?Product
    {
        if (!Feature::driver('array')->active(BillingEnabled::class)) {
            return null;
        }

        $organizationModel = Organization::findOrFail($organization->id);
        $subscription = $organizationModel->subscription('plan');

        if (!$subscription) {
            return null;
        }

        $product = StripeProduct::query()
            ->whereIn(
                'stripe_id',
                $subscription
                    ->items()
                    ->pluck('stripe_product')
            )
            ->whereNotNull('stripe_base_price_id')
            ->first();

        return $product ? Product::from($product) : null;
    }
}
