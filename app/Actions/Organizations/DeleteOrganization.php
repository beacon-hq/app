<?php

declare(strict_types=1);

namespace App\Actions\Organizations;

use App\Actions\Traits\AsAction;
use App\Models\Organization as OrganizationModel;
use App\Values\Organization;

class DeleteOrganization
{
    use AsAction;

    public function handle(Organization $organization): void
    {
        $organizationModel = OrganizationModel::where('id', $organization->id)->firstOrFail();
        $organizationModel->delete();
    }
}
