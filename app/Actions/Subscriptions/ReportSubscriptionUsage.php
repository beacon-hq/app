<?php

declare(strict_types=1);

namespace App\Actions\Subscriptions;

use App\Actions\Traits\AsAction;
use App\Features\BillingEnabled;
use App\Models\Organization;
use App\Values\Organization as OrganizationValue;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Cashier;
use Laravel\Pennant\Feature;
use Stripe\StripeClient;

class ReportSubscriptionUsage
{
    use AsAction;

    public function handle(string $id, OrganizationValue $organization, int $quantity = 1): bool
    {
        try {
            // Skip if billing is not enabled
            if (!Feature::driver('array')->active(BillingEnabled::class)) {
                return false;
            }

            // Get the organization
            $organizationModel = Organization::find($organization->id);
            if (!$organizationModel) {
                return false;
            }

            // Report usage to Stripe
            $this->getStripeClient()->billing->meterEvents->create([
                'event_name' => 'flag_evaluations',
                'payload' => [
                    'evaluations' => $quantity,
                    'stripe_customer_id' => $organizationModel->stripe_id,
                ],
                'identifier' => $id,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to report usage to Stripe: ' . $e->getMessage(), [
                'exception' => $e,
                'organization_id' => $organization->id,
                'quantity' => $quantity,
            ]);

            return false;
        }
    }

    protected function getStripeClient(): StripeClient
    {
        return app()->has(StripeClient::class) ? app(StripeClient::class) : Cashier::stripe();
    }
}
