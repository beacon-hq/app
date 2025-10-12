<?php

declare(strict_types=1);

namespace App\Actions\Subscriptions;

use App\Actions\Traits\AsAction;
use App\Models\Organization;
use App\Values\Organization as OrganizationValue;

class HasActiveSubscription
{
    use AsAction;

    public function handle(OrganizationValue $organization): bool
    {
        $organizationModel = Organization::findOrFail($organization->id);

        return $organizationModel->subscriptions()
            ->whereIn('stripe_status', ['active', 'trialing'])
            ->exists();
    }
}
