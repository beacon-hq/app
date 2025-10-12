<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\AppContext\SetOrganizationContext;
use App\Actions\AppContext\SetTeamContext;
use App\Actions\Organizations\FindOrganizationById;
use App\Actions\Teams\ListTeams;
use App\Values\Organization;
use Bag\Attributes\WithoutValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;

class OrganizationSelectController
{
    public function update(
        #[WithoutValidation]
        Organization $organization
    ): RedirectResponse {
        SetOrganizationContext::run(FindOrganizationById::run($organization->id));

        $team = ListTeams::run()->first();
        Session::put('team', $team);
        SetTeamContext::run($team);

        return \redirect()->back()->withAlert('success', 'Organization changed successfully.');
    }
}
