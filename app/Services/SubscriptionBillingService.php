<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\OrganizationRepository;
use App\Repositories\ProductRepository;
use App\Repositories\SubscriptionBillingRepository;
use App\Values\Organization;
use App\Values\Product;
use App\Values\Subscription;
use Brick\Money\Money;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Checkout;
use Stripe\StripeClient;

class SubscriptionBillingService
{
    public function __construct(
        protected ProductRepository $productRepository,
        protected OrganizationRepository $organizationRepository,
        protected SubscriptionBillingRepository $subscriptionBillingRepository,
    ) {
    }

    public function getSubscription(Organization $organization): Subscription
    {
        return Subscription::from($this->subscriptionBillingRepository->getSubscription($organization));
    }

    public function hasActiveSubscription(Organization $organization): bool
    {
        return $this->subscriptionBillingRepository->hasActiveSubscription($organization);
    }

    public function isTrialSubscription(Organization $organization): bool
    {
        return $this->subscriptionBillingRepository->isTrialSubscription($organization);
    }

    public function getTrialEndDate(Organization $organization): ?\DateTimeInterface
    {
        return $this->isTrialSubscription($organization) ? $this->subscriptionBillingRepository->getTrialEndDate($organization) : null;
    }

    public function getPlan(Organization $organization): Product
    {
        return Product::from($this->subscriptionBillingRepository->getPlan($organization));
    }

    public function changeSubscription(Organization $organization, Product $product): bool
    {
        return $this->subscriptionBillingRepository->changeSubscription($organization, $product->id);
    }

    public function reportUsage(string $id, Organization $organization, int $quantity = 1, ?\DateTimeInterface $timestamp = null): bool
    {
        try {
            // Skip if billing is not enabled
            if (!config('beacon.billing.enabled', false)) {
                return false;
            }

            // Get the organization
            $organization = $this->organizationRepository->findById($organization->id);
            if (!$organization) {
                return false;
            }

            // Report usage to Stripe
            $this->getStripeClient()->billing->meterEvents->create([
                'event_name' => 'flag_evaluations',
                'payload' => [
                    'evaluations' => $quantity,
                    'stripe_customer_id' => $organization->stripe_id,
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

    public function setFraudThreshold(string $subscriptionId, ?string $productId = null, bool $isTrial = false): void
    {
        $thresholdAmount = $this->getThresholdAmount($productId);

        $this->getStripeClient()->subscriptions->update($subscriptionId, [
            'billing_thresholds' => [
                'amount_gte' => $thresholdAmount->getMinorAmount()->toInt(),
            ],
        ]);
    }

    public function createSubscription(Organization $organization, Product $product): Checkout
    {
        return $this->subscriptionBillingRepository->createSubscription($organization, $product->id);
    }

    public function getPeriodStartDate(Organization $organization): CarbonImmutable
    {
        return $this->subscriptionBillingRepository->getPeriodStartDate($organization);
    }

    public function getPeriodEndDate(Organization $organization): CarbonImmutable
    {
        return $this->subscriptionBillingRepository->getPeriodEndDate($organization);
    }

    public function predictNextBill(array $planMetrics, Product $product): array
    {
        return $this->subscriptionBillingRepository->predictNextBill($planMetrics, $product);
    }

    public function getInvoices(Organization $organization)
    {
        return $this->subscriptionBillingRepository->getInvoices($organization);
    }

    public function cancelSubscription(Organization $organization): bool
    {
        return $this->subscriptionBillingRepository->cancelSubscription($organization);
    }

    public function getSubscriptionStatus(Organization $organization): array
    {
        return $this->subscriptionBillingRepository->getSubscriptionStatus($organization);
    }

    public function resumeSubscription(Organization $organization, string $billing): bool
    {
        return $this->subscriptionBillingRepository->resumeSubscription($organization, $billing);
    }

    protected function getStripeClient(): StripeClient
    {
        return app()->has(StripeClient::class) ? app(StripeClient::class) : Cashier::stripe();
    }

    protected function getThresholdAmount(?string $productId = null): Money
    {
        // Default threshold from config
        $defaultThreshold = config('beacon.billing.trial_fraud_limit');

        if ($productId === null) {
            return Money::of($defaultThreshold, 'USD');
        }

        try {
            $product = Product::fromModel($this->productRepository->find($productId));

            if (isset($product->entitlements['evaluations'])) {
                $evaluations = (int) $product->entitlements['evaluations'];

                // Scale threshold based on evaluations allowed
                return match (true) {
                    $evaluations >= 2000000 => Money::of(2000, 'USD'), // Enterprise
                    $evaluations >= 500000 => Money::of(500, 'USD'), // Scale
                    $evaluations >= 100000 => Money::of(150, 'USD'), // Growth
                    default  => Money::of(50, 'USD'),  // Solo
                };
            }
        } catch (\Throwable) {
        }

        return Money::of($defaultThreshold, 'USD');
    }
}
