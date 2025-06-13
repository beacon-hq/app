<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ProductRepository;
use App\Values\Product;
use Brick\Money\Money;
use Laravel\Cashier\Cashier;
use Stripe\StripeClient;

class SubscriptionBillingService
{
    public function __construct(
        protected ProductRepository $productRepository,
    ) {
    }

    /**
     * Set billing thresholds for a subscription based on its plan.
     *
     * @param string $subscriptionId The Stripe subscription ID
     * @param string|null $productId The product/plan ID
     * @param bool $isTrial Whether the subscription is in trial mode
     */
    public function setFraudThreshold(string $subscriptionId, ?string $productId = null, bool $isTrial = false): void
    {
        $thresholdAmount = $this->getThresholdAmount($productId);

        $this->getStripeClient()->subscriptions->update($subscriptionId, [
            'billing_thresholds' => [
                'amount_gte' => $thresholdAmount->getMinorAmount()->toInt(),
            ],
        ]);
    }

    /**
     * Get the Stripe client to use for API calls.
     *
     */
    protected function getStripeClient(): StripeClient
    {
        return app(StripeClient::class) ?? Cashier::stripe();
    }

    /**
     * Get the appropriate threshold amount based on the product/plan.
     *
     * @param string|null $productId The product/plan ID
     * @return Money The threshold amount
     */
    protected function getThresholdAmount(?string $productId = null): Money
    {
        // Default threshold from config
        $defaultThreshold = config('beacon.billing.trial_fraud_limit');

        if ($productId === null) {
            return Money::of($defaultThreshold, 'USD');
        }

        try {
            $product = Product::fromModel($this->productRepository->find($productId));

            if (isset($product->entitlements['evaluations'])) {
                $evaluations = (int) $product->entitlements['evaluations'];

                // Scale threshold based on evaluations allowed
                return match (true) {
                    $evaluations >= 2000000 => Money::of(2000, 'USD'), // Enterprise
                    $evaluations >= 500000 => Money::of(500, 'USD'), // Scale
                    $evaluations >= 100000 => Money::of(150, 'USD'), // Growth
                    default  => Money::of(50, 'USD'),  // Solo
                };
            }
        } catch (\Throwable) {
        }

        return Money::of($defaultThreshold, 'USD');
    }
}
