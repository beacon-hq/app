<?php

declare(strict_types=1);

namespace App\Actions\Subscriptions;

use App\Actions\Traits\AsAction;
use App\Models\Organization as OrganizationModel;
use App\Values\Organization;

class GetSubscriptionStatus
{
    use AsAction;

    public function __construct(
        protected HasActiveSubscription $hasActiveSubscription,
        protected IsTrialSubscription $isTrialSubscription
    ) {
    }

    public function handle(Organization $organization): array
    {
        $subscription = OrganizationModel::find($organization->id)->subscription('plan');

        return [
            'active' => $this->hasActiveSubscription->handle($organization),
            'grace_period' => $subscription->onGracePeriod(),
            'trialing' => $this->isTrialSubscription->handle($organization),
            'ends_at' => $subscription->ends_at,
        ];
    }
}
