<?php

declare(strict_types=1);

namespace App\Actions\AppContext;

use App\Actions\Traits\AsAction;
use App\Events\OrganizationChangedEvent;
use App\Models\Organization;
use App\Values\Organization as OrganizationValue;
use Illuminate\Support\Facades\Context;

class SetOrganizationContext
{
    use AsAction;

    public function handle(Organization|OrganizationValue|null $organization): ?OrganizationValue
    {
        if ($organization instanceof Organization) {
            $organization = OrganizationValue::from(...OrganizationValue::fromModel($organization));
        }

        $previousOrgId = Context::has('organization') ? Context::get('organization')?->id : null;

        Context::add('organization', $organization);

        if ($organization !== null && ($previousOrgId === null || $previousOrgId !== $organization->id)) {
            OrganizationChangedEvent::dispatch($organization);
        }

        return $organization;
    }
}
