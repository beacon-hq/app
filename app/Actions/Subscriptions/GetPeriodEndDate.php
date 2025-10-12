<?php

declare(strict_types=1);

namespace App\Actions\Subscriptions;

use App\Actions\Traits\AsAction;
use App\Models\Organization as OrganizationModel;
use App\Values\Organization;
use Carbon\CarbonImmutable;

class GetPeriodEndDate
{
    use AsAction;

    public function __construct(protected IsTrialSubscription $isTrialSubscription)
    {
    }

    public function handle(Organization $organization): CarbonImmutable
    {
        $isTrialSubscription = $this->isTrialSubscription->handle($organization);
        $organizationModel = OrganizationModel::find($organization->id);
        $subscription = $organizationModel->subscription('plan')->asStripeSubscription();

        if (!$isTrialSubscription) {
            return CarbonImmutable::createFromTimestamp($subscription->current_period_end);
        }

        $startDate = CarbonImmutable::createFromTimestamp($subscription->current_period_start);

        return $startDate->addMonths($startDate->diff(now())->m + 1);
    }
}
