<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Actions\Subscriptions\SetFraudThreshold;
use Laravel\Cashier\Events\WebhookHandled;

class StripeCustomerSubscriptionCreatedWebhookListener
{
    public function handle(WebhookHandled $event): void
    {
        if ($event->payload['type'] === 'customer.subscription.created' || $event->payload['type'] === 'customer.subscription.updated') {
            SetFraudThreshold::run(
                subscriptionId: $event->payload['data']['object']['id'],
                productId: $event->payload['data']['object']['items']['data'][0]['price']['product'] ?? null,
            );
        }
    }
}
