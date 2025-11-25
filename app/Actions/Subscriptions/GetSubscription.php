<?php

declare(strict_types=1);

namespace App\Actions\Subscriptions;

use App\Actions\Traits\AsAction;
use App\Models\Organization as OrganizationModel;
use App\Values\Organization;
use App\Values\Subscription;

class GetSubscription
{
    use AsAction;

    public function handle(Organization $organization): Subscription
    {
        $subscription = OrganizationModel::findOrFail($organization->id)
            ->subscription('plan');

        return Subscription::from($subscription);
    }
}
