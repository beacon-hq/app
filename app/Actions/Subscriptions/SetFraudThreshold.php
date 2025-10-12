<?php

declare(strict_types=1);

namespace App\Actions\Subscriptions;

use App\Actions\Traits\AsAction;
use App\Models\StripeProduct;
use Brick\Money\Money;
use Laravel\Cashier\Cashier;
use Stripe\StripeClient;

class SetFraudThreshold
{
    use AsAction;

    public function handle(string $subscriptionId, ?string $productId = null): void
    {
        $thresholdAmount = $this->getThresholdAmount($productId);

        $this->getStripeClient()->subscriptions->update($subscriptionId, [
            'billing_thresholds' => [
                'amount_gte' => $thresholdAmount->getMinorAmount()->toInt(),
            ],
        ]);
    }

    protected function getStripeClient(): StripeClient
    {
        return app()->has(StripeClient::class) ? app(StripeClient::class) : Cashier::stripe();
    }

    protected function getThresholdAmount(?string $productId = null): mixed
    {
        // Default threshold from config
        $defaultThreshold = config('beacon.billing.trial_fraud_limit');

        if ($productId === null) {
            return Money::of($defaultThreshold, 'USD');
        }

        try {
            $product = StripeProduct::query()->find($productId);

            if ($product && isset($product->entitlements['evaluations'])) {
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
