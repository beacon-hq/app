<?php

declare(strict_types=1);

namespace App\Listeners;

use Brick\Money\Money;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Events\WebhookHandled;

class StripeCustomerSubscriptionCreatedWebhookListener
{
    public function handle(WebhookHandled $event): void
    {
        if ($event->payload['type'] === 'customer.subscription.created') {
            $subscriptionId = $event->payload['data']['object']['id'];
            if ($event->payload['data']['object']['status'] === 'trialing') {
                Cashier::stripe()->subscriptions->update($subscriptionId, [
                    'billing_thresholds' => [
                        'amount_gte' => Money::of(config('beacon.billing.trial_fraud_limit'), 'USD')->getMinorAmount()->toInt(),
                    ],
                ]);
            }
        }
    }
}
