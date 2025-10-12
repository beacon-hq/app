<?php

declare(strict_types=1);

namespace App\Actions\Subscriptions;

use App\Actions\Traits\AsAction;
use App\Models\Organization as OrganizationModel;
use App\Values\Organization;
use Carbon\CarbonImmutable;

class GetTrialEndDate
{
    use AsAction;

    public function handle(Organization $organization): ?\DateTimeInterface
    {
        if (!IsTrialSubscription::run($organization)) {
            return null;
        }

        $organizationModel = OrganizationModel::find($organization->id);
        $subscription = $organizationModel->subscription('plan');

        return CarbonImmutable::createFromTimestamp($subscription->asStripeSubscription()->current_period_end);
    }
}
