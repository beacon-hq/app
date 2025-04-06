<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App;
use App\Services\TeamService;
use App\Values\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;
use Session;

class TeamSelectController extends Controller
{
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
        $team = $teamService->find($team->id);

        Session::put('team', $team);
        App::context(organization: $team->organization, team: $team);

        if ($request->json('previous') !== null) {
            return \redirect()->to($request->json('previous'))->withAlert('success', 'Team changed successfully.');
        }

        return \redirect()->back()->getTargetUrl() !== URL::route('teams.select') ? redirect()->back()->withAlert('success', 'Team changed successfully.') : \redirect()->to(route('dashboard'));
    }
}
