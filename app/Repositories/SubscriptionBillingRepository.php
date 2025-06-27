<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Organization;
use App\Models\StripeProduct;
use App\Values\Organization as OrganizationValue;
use App\Values\Product;
use Brick\Money\Money;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Checkout;
use Laravel\Cashier\Invoice;
use Laravel\Cashier\Subscription;

class SubscriptionBillingRepository
{
    public function __construct(protected ProductRepository $productRepository)
    {
    }

    public function hasActiveSubscription(OrganizationValue $organization): bool
    {
        $organization = Organization::findOrFail($organization->id);

        return $organization->subscriptions()->whereIn('stripe_status', ['active', 'trialing'])->exists();
    }

    public function isTrialSubscription(OrganizationValue $organization): bool
    {
        $organization = Organization::findOrFail($organization->id);

        return $organization->subscriptions()->where('stripe_status', 'trialing')->exists();
    }

    public function getPlan(OrganizationValue $organization): StripeProduct
    {
        $organization = Organization::findOrFail($organization->id);

        $subscription = $organization->subscription('plan');

        return StripeProduct::query()
            ->whereIn(
                'stripe_id',
                $subscription
                    ->items()
                    ->pluck('stripe_product')
            )
            ->whereNotNull('stripe_base_price_id')
            ->first();
    }

    public function createSubscription(OrganizationValue $organization, string $productId): Checkout
    {
        $organization = Organization::findOrFail($organization->id);
        $product = $this->productRepository->find($productId);

        $checkout = $organization->newSubscription('plan');

        return $checkout
            ->alwaysInvoice()
            ->price($product->basePrice->stripe_id)
            ->meteredPrice($product->meteredPrice->stripe_id)
            ->when(isset($product->entitlements['trial_length']), function ($checkout) use ($organization, $product) {
                $trialLength = new CarbonInterval($product->entitlements['trial_length']);

                $subscription = $organization->subscriptions()->whereNotNull('trial_ends_at')->latest()->first();
                if ($subscription !== null) {
                    if ($subscription->trial_ends_at->lessThan($subscription->updated_at)) {
                        return $checkout;
                    }
                    $trialLength = $subscription->updated_at->diff($subscription->trial_ends_at);
                }

                return $checkout->trialUntil(now()->add($trialLength)->endOfDay());
            })
            ->checkout([
                'success_url' => route('dashboard'),
                'cancel_url' => route('checkout.index'),
            ], [
                'description' => $product->name,
                'metadata' => [
                    'id' => $organization->id,
                ],
            ]);
    }

    public function changeSubscription(OrganizationValue $organization, string $productId): bool
    {
        try {
            $organization = Organization::findOrFail($organization->id);
            $product = $this->productRepository->find($productId);

            // Get the current subscription
            $subscription = $organization->subscription('plan');

            if (!$subscription) {
                return false;
            }

            // Swap to the new prices
            $subscription->skipTrial()->swap([
                $product->basePrice->stripe_id,
                $product->meteredPrice->stripe_id,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to change subscription: ' . $e->getMessage(), [
                'exception' => $e,
                'organization_id' => $organization->id,
                'product_id' => $productId,
            ]);

            return false;
        }
    }

    public function getTrialEndDate(OrganizationValue $organization): ?CarbonImmutable
    {
        if (!$this->isTrialSubscription($organization)) {
            return null;
        }

        $organization = Organization::find($organization->id);
        $subscription = $organization->subscription('plan');

        return CarbonImmutable::createFromTimestamp($subscription->asStripeSubscription()->current_period_end);
    }

    public function getPeriodEndDate(OrganizationValue $organization): CarbonImmutable
    {
        if (!$this->isTrialSubscription($organization)) {
            $organization = Organization::find($organization->id);
            $subscription = $organization->subscription('plan')->asStripeSubscription();

            return CarbonImmutable::createFromTimestamp($subscription->current_period_end);
        }

        $organization = Organization::find($organization->id);
        $subscription = $organization->subscription('plan')->asStripeSubscription();

        $startDate = CarbonImmutable::createFromTimestamp($subscription->current_period_start);

        return $startDate->addMonths($startDate->diff(now())->m + 1);
    }

    public function getPeriodStartDate(OrganizationValue $organization)
    {
        if (!$this->isTrialSubscription($organization)) {
            $organization = Organization::find($organization->id);
            $subscription = $organization->subscription('plan')->asStripeSubscription();

            return CarbonImmutable::createFromTimestamp($subscription->current_period_start);
        }

        $organization = Organization::find($organization->id);
        $subscription = $organization->subscription('plan')->asStripeSubscription();

        $startDate = CarbonImmutable::createFromTimestamp($subscription->current_period_start);

        return $startDate->addMonths($startDate->diff(now())->m);
    }

    public function predictNextBill(array $planMetrics, Product $product): array
    {
        if ($planMetrics['evaluations']['projections']['date']['projected_total'] > $product->entitlements['evaluations']) {
            $overage = $product
                ->meteredPrice
                ->multipliedBy(ceil(($planMetrics['evaluations']['projections']['date']['projected_total'] - $product->entitlements['evaluations']) / 1000));

            return [
                'total' => $overage
                    ->plus($product->basePrice)
                    ->formatTo('en_US', true),
                'base' => $product->basePrice->formatTo('en_US', true),
                'metered' => $overage->formatTo('en_US', true),
            ];
        } else {
            return [
                'total' => $product->basePrice->formatTo('en_US', true),
                'base' => $product->basePrice->formatTo('en_US', true),
                'metered' => Money::of(0, 'USD')->formatTo('en_US', true),
            ];
        }
    }

    public function getInvoices(OrganizationValue $organization): Collection
    {
        $organization = Organization::find($organization->id);

        return $organization->invoices()->map(function (Invoice $invoice) {
            return [
                'id' => $invoice->id,
                'number' => $invoice->number,
                'date' => $invoice->date()->toFormattedDateString(),
                'total' => $invoice->total(),
                'status' => $invoice->asStripeInvoice()->paid ? 'Paid' : 'Outstanding',
            ];
        });
    }

    public function cancelSubscription(OrganizationValue $organization): bool
    {
        Organization::find($organization->id)->subscription('plan')->cancel();

        return true;
    }

    public function getSubscriptionStatus(OrganizationValue $organization): array
    {
        $subscription = Organization::find($organization->id)->subscription('plan');

        return [
            'active' => $this->hasActiveSubscription($organization),
            'grace_period' => $subscription->onGracePeriod(),
            'trialing' => $this->isTrialSubscription($organization),
            'ends_at' => $subscription->ends_at,
        ];
    }

    public function resumeSubscription(OrganizationValue $organization, string $billing)
    {
        $subscription = Organization::find($organization->id)->subscriptions()->findOrFail($billing);

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

    public function getSubscription(OrganizationValue $organization): Subscription
    {
        return Organization::findOrFail($organization->id)
            ->subscription('plan');
    }
}
