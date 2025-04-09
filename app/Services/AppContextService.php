<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Organization;
use App\Models\Team;
use App\Values\AppContext;
use App\Values\Organization as OrganizationValue;
use App\Values\Team as TeamValue;
use Illuminate\Support\Facades\App;

class AppContextService
{
    public function getContext(): AppContext
    {
        return App::context();
    }

    public function setOrganization(Organization|OrganizationValue $organization): void
    {
        App::context(organization: $organization);
    }

    public function setTeam(Team|TeamValue $team): void
    {
        App::context(team: $team);
    }
}
