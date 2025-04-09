<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AppContextService;
use App\Services\OrganizationService;
use App\Services\TeamService;
use App\Values\Organization;
use Bag\Attributes\WithoutValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;

class OrganizationSelectController extends Controller
{
    public function __construct(protected OrganizationService $organizationService, protected TeamService $teamService, protected AppContextService $appContextService)
    {
    }

    public function update(
        #[WithoutValidation]
        Organization $organization
    ): RedirectResponse {
        $this->appContextService->setOrganization($this->organizationService->findById($organization->id));

        $team = $this->teamService->all()->first();
        Session::put('team', $team);
        $this->appContextService->setTeam($team);

        return \redirect()->back()->withAlert('success', 'Organization changed successfully.');
    }
}
