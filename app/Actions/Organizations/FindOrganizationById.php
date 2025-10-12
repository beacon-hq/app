<?php

declare(strict_types=1);

namespace App\Actions\Organizations;

use App\Actions\Traits\AsAction;
use App\Models\Organization as OrganizationModel;
use App\Values\Organization;
use App\Values\User;

class FindOrganizationById
{
    use AsAction;

    public function handle(?string $id): Organization
    {
        $organization = OrganizationModel::findOrFail($id);

        return Organization::from(
            id: $organization->id,
            owner: $organization->owner !== null ? User::from($organization->owner) : null,
            name: $organization->name
        );
    }
}
