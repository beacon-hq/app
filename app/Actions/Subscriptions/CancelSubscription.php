<?php

declare(strict_types=1);

namespace App\Actions\Subscriptions;

use App\Actions\Traits\AsAction;
use App\Models\Organization as OrganizationModel;
use App\Values\Organization;

class CancelSubscription
{
    use AsAction;

    public function handle(Organization $organization): bool
    {
        OrganizationModel::find($organization->id)->subscription('plan')->cancel();

        return true;
    }
}
