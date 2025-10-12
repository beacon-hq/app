<?php

declare(strict_types=1);

namespace App\Actions\Subscriptions;

use App\Actions\Traits\AsAction;
use App\Models\Organization as OrganizationModel;
use App\Values\Organization;
use Illuminate\Support\Facades\Log;

class ResumeSubscription
{
    use AsAction;

    public function handle(Organization $organization, string $billing): bool
    {
        $subscription = OrganizationModel::find($organization->id)->subscriptions()->findOrFail($billing);

        if (($remainingDays = $subscription->trial_ends_at->diffInDays($subscription->updated_at)) > 0) {
            $newTrialEndDate = $subscription->trial_ends_at->addDays($remainingDays);

            $subscription = $subscription->resume();
            $subscription = $subscription->extendTrial($newTrialEndDate);
        } else {
            $subscription = $subscription->resume();
        }

        if ($subscription->stripe_status === 'active') {
            return true;
        } else {
            Log::error('Failed to resume subscription', [
                'organization_id' => $organization->id,
                'billing' => $billing,
                'status' => $subscription->stripe_status,
            ]);

            return false;
        }
    }
}
