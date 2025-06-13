<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Organization;
use App\Models\Scopes\CurrentOrganizationScope;
use App\Values\Organization as OrganizationValue;
use App\Values\User;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Cashier\Checkout;

class OrganizationRepository
{
    public function __construct(protected ProductRepository $productRepository)
    {
    }

    public function create(User $user, OrganizationValue $organization): Organization
    {
        $organization = Organization::create([
            'owner_id' => $user->id,
            'name' => $organization->name,
        ]);

        $organization->users()->attach($user->id);

        return $organization;
    }

    public function update(OrganizationValue $organization): Organization
    {
        $organizationModel = Organization::where('id', $organization->id)->firstOrFail();
        $organizationModel->update([
            'name' => $organization->name,
        ]);

        return $organizationModel->fresh();
    }

    public function all(User $user): Collection
    {
        $query = Organization::whereHas(
            'teams',
            fn (Builder $query) => $query
                ->withoutGlobalScopes([CurrentOrganizationScope::class])
                ->whereHas(
                    'users',
                    fn (Builder $query) => $query
                        ->withoutGlobalScopes([CurrentOrganizationScope::class])
                        ->where('id', $user->id)
                )
        );

        return $query->get();
    }

    public function findById(?string $id): Organization
    {
        return Organization::findOrFail($id);
    }

    public function delete(OrganizationValue $organization): void
    {
        $organizationModel = Organization::where('id', $organization->id)->firstOrFail();
        $organizationModel->delete();
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
}
