<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Organization;
use App\Models\Scopes\CurrentOrganizationScope;
use App\Values\Collections\OrganizationCollection;
use App\Values\Organization as OrganizationValue;
use App\Values\User;
use Illuminate\Database\Eloquent\Builder;

class OrganizationRepository
{
    public function create(User $user, OrganizationValue $organization): OrganizationValue
    {
        $organization = Organization::create([
            'owner_id' => $user->id,
            'name' => $organization->name,
        ]);

        $organization->users()->attach($user->id);

        return OrganizationValue::from($organization);
    }

    public function update(OrganizationValue $organization): OrganizationValue
    {
        $organizationModel = Organization::where('id', $organization->id)->firstOrFail();
        $organizationModel->update([
            'name' => $organization->name,
        ]);

        return OrganizationValue::from($organizationModel);
    }

    public function all(User $user): OrganizationCollection
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

        return OrganizationValue::collect(
            $query->get()
        );
    }

    public function findById(?string $id): OrganizationValue
    {
        return OrganizationValue::from(Organization::findOrFail($id));
    }

    public function delete(OrganizationValue $organization): void
    {
        $organizationModel = Organization::where('id', $organization->id)->firstOrFail();
        $organizationModel->delete();
    }
}
