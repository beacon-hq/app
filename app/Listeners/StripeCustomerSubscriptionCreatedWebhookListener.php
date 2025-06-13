<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Services\SubscriptionBillingService;
use Laravel\Cashier\Events\WebhookHandled;

class StripeCustomerSubscriptionCreatedWebhookListener
{
    public function __construct(
        protected SubscriptionBillingService $subscriptionBillingService
    ) {
    }

    public function handle(WebhookHandled $event): void
    {
        if ($event->payload['type'] === 'customer.subscription.created' || $event->payload['type'] === 'customer.subscription.updated') {
            $this->subscriptionBillingService->setFraudThreshold(
                subscriptionId: $event->payload['data']['object']['id'],
                productId: $event->payload['data']['object']['items']['data'][0]['price']['product'] ?? null,
                isTrial: $event->payload['data']['object']['status'] === 'trialing'
            );
        }
    }
}
