<?php

declare(strict_types=1);

namespace App\Actions\Subscriptions;

use App\Actions\Traits\AsAction;
use App\Models\Organization as OrganizationModel;
use App\Models\StripeProduct;
use App\Values\Organization;
use App\Values\Product;
use Illuminate\Support\Facades\Log;

class ChangeSubscription
{
    use AsAction;

    public function handle(Organization $organization, Product $product): bool
    {
        try {
            $organizationModel = OrganizationModel::findOrFail($organization->id);
            $productModel = StripeProduct::query()
                ->with(['basePrice', 'meteredPrice'])
                ->findOrFail($product->id);

            // Get the current subscription
            $subscription = $organizationModel->subscription('plan');

            if (!$subscription) {
                return false;
            }

            // Swap to the new prices
            $subscription->skipTrial()->swap([
                $productModel->basePrice->stripe_id,
                $productModel->meteredPrice->stripe_id,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to change subscription: ' . $e->getMessage(), [
                'exception' => $e,
                'organization_id' => $organization->id,
                'product_id' => $product->id,
            ]);

            return false;
        }
    }
}
