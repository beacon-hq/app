<?php

declare(strict_types=1);

namespace App\Actions\Subscriptions;

use App\Actions\Traits\AsAction;
use App\Models\Organization;
use App\Values\Organization as OrganizationValue;

class IsTrialSubscription
{
    use AsAction;

    public function handle(OrganizationValue $organization): bool
    {
        $organizationModel = Organization::findOrFail($organization->id);

        return $organizationModel->subscriptions()
            ->where('stripe_status', 'trialing')
            ->exists();
    }
}
