<?php

declare(strict_types=1);

namespace App\Services;

use App;
use App\Models\StripeProduct;
use App\Repositories\OrganizationRepository;
use App\Values\Collections\OrganizationCollection;
use App\Values\Organization;
use App\Values\User;
use Carbon\CarbonInterval;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Checkout;

class OrganizationService
{
    public function __construct(protected OrganizationRepository $organizationRepository)
    {
    }

    public function create(User $user, Organization $organization): Organization
    {
        $organizationModel = $this->organizationRepository->create($user, $organization);

        return Organization::from(
            id: $organizationModel->id,
            owner: $organizationModel->owner !== null ? User::from($organizationModel->owner) : null,
            name: $organizationModel->name
        );
    }

    public function update(Organization $organization): Organization
    {
        $updatedOrganization = $this->organizationRepository->update($organization);

        return Organization::from(
            id: $updatedOrganization->id,
            owner: $updatedOrganization->owner !== null ? User::from($updatedOrganization->owner) : null,
            name: $updatedOrganization->name
        );
    }

    public function all(User $user): OrganizationCollection
    {
        $organizations = $this->organizationRepository->all($user);

        return Organization::collect($organizations);
    }

    public function findById(?string $id): Organization
    {
        $organization = $this->organizationRepository->findById($id);

        return Organization::from(
            id: $organization->id,
            owner: $organization->owner !== null ? User::from($organization->owner) : null,
            name: $organization->name
        );
    }

    public function delete(Organization $organization): void
    {
        $this->organizationRepository->delete($organization);
    }

    public function createSubscription(Organization $organization, App\Values\Product $product): Checkout
    {
        $product = StripeProduct::find($product->id);

        $organization = auth()
            ->user()
            ->organizations()
            ->findOrFail($organization->id);

        $checkout = $organization
            ->newSubscription('plan');

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

    public function hasActiveSubscription(Organization $organization)
    {
        $organization = App\Models\Organization::findOrFail($organization->id);

        Cashier::stripe();

        return $organization->subscriptions()->whereIn('stripe_status', ['active', 'trialing'])->exists();
    }
}
