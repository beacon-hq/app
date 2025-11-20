<?php

declare(strict_types=1);

namespace App\Actions\Subscriptions;

use App\Actions\Traits\AsAction;
use App\Models\Organization as OrganizationModel;
use App\Values\Organization;

class GetSubscriptionStatus
{
    use AsAction;

    public function handle(Organization $organization): array
    {
        $subscription = OrganizationModel::find($organization->id)->subscription('plan');

        return [
            'active' => HasActiveSubscription::run($organization),
            'grace_period' => $subscription->onGracePeriod(),
            'trialing' => IsTrialSubscription::run($organization),
            'ends_at' => $subscription->ends_at,
        ];
    }
}
