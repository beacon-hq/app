<?php

declare(strict_types=1);

namespace App\Actions\Organizations;

use App\Actions\Traits\AsAction;
use App\Models\Organization as OrganizationModel;
use App\Values\Organization;
use App\Values\User;

class UpdateOrganization
{
    use AsAction;

    public function handle(Organization $organization): Organization
    {
        $organizationModel = OrganizationModel::where('id', $organization->id)->firstOrFail();
        $organizationModel->update([
            'name' => $organization->name,
        ]);

        $updatedOrganization = $organizationModel->fresh();

        return Organization::from(
            id: $updatedOrganization->id,
            owner: $updatedOrganization->owner !== null ? User::from($updatedOrganization->owner) : null,
            name: $updatedOrganization->name
        );
    }
}
