<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Organization;
use App\Models\Scopes\CurrentOrganizationScope;
use App\Values\Organization as OrganizationValue;
use App\Values\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class OrganizationRepository
{
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
}
