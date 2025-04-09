<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AppContextService;
use App\Services\TeamService;
use App\Values\Organization;
use App\Values\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;

class TeamSelectController extends Controller
{
    public function __construct(protected AppContextService $appContextService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('Teams/Select');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Team $team, TeamService $teamService, Request $request): RedirectResponse
    {
        $team = $teamService->find($team->id, Organization::collect(Auth::user()->organizations));

        Session::put('team', $team);
        $this->appContextService->setOrganization($team->organization);
        $this->appContextService->setTeam($team);

        if ($request->json('previous') !== null) {
            return \redirect()->to($request->json('previous'))->withAlert('success', 'Team changed successfully.');
        }

        return \redirect()->back()->getTargetUrl() !== URL::route('teams.select') ? redirect()->back()->withAlert('success', 'Team changed successfully.') : \redirect()->to(route('dashboard'));
    }
}
