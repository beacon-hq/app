<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App;
use App\Services\OrganizationService;
use App\Services\TeamService;
use App\Values\Organization;
use Bag\Attributes\WithoutValidation;
use Session;

class OrganizationSelectController extends Controller
{
    public function __construct(protected OrganizationService $organizationService, protected TeamService $teamService)
    {
    }

    public function update(
        #[WithoutValidation]
        Organization $organization
    ) {
        App::context(organization: $this->organizationService->findById($organization->id));

        $team = $this->teamService->all()->first();
        Session::put('team', $team);
        App::context(team: $team);

        return \redirect()->back()->withAlert('success', 'Organization changed successfully.');
    }
}
