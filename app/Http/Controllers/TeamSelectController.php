<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App;
use App\Services\TeamService;
use App\Values\Team;
use Inertia\Inertia;
use Session;

class TeamSelectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Teams/Select');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Team $team, TeamService $teamService)
    {
        $team = $teamService->findById($team->id);

        Session::put('team', $team);
        App::context(team: $team);

        return request()->routeIs('teams.choose', 'teams.select') ? redirect('dashboard') : redirect()->back();
    }
}
