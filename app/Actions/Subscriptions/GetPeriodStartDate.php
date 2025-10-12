<?php

declare(strict_types=1);

namespace App\Actions\Subscriptions;

use App\Actions\Traits\AsAction;
use App\Models\Organization;
use App\Values\Organization as OrganizationValue;
use Carbon\CarbonImmutable;

class GetPeriodStartDate
{
    use AsAction;

    public function handle(OrganizationValue $organization): CarbonImmutable
    {
        $isTrialSubscription = IsTrialSubscription::run($organization);
        $organizationModel = Organization::find($organization->id);
        $subscription = $organizationModel->subscription('plan')->asStripeSubscription();

        if (!$isTrialSubscription) {
            return CarbonImmutable::createFromTimestamp($subscription->current_period_start);
        }

        $startDate = CarbonImmutable::createFromTimestamp($subscription->current_period_start);

        return $startDate->addMonths($startDate->diff(now())->m);
    }
}
