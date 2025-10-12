<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Teams\CreateTeam;
use App\Actions\Teams\FindTeam;
use App\Actions\Teams\GetTeamMembers;
use App\Actions\Teams\GetTeamNonMembers;
use App\Actions\Teams\ListTeams;
use App\Actions\Teams\UpdateTeam;
use App\Values\Organization;
use App\Values\Team;
use App\Values\User;
use Bag\Attributes\WithoutValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TeamController
{
    public function __construct()
    {
    }

    public function index(): Response
    {
        Gate::authorize('viewAny', Team::class);

        return Inertia::render('Teams/Index', [
            'teams' => ListTeams::run(Auth::user()->id, true),
        ]);
    }

    public function store(Team $team): RedirectResponse
    {
        Gate::authorize('create', Team::class);

        $team = CreateTeam::run($team, User::from(Auth::user()));

        return redirect(route('teams.edit', ['team' => $team->id]))
            ->withAlert('success', 'Team Created Successfully.');
    }

    public function edit(
        #[WithoutValidation]
        Team $team,
        Request $request
    ): Response {
        Gate::authorize('update', $team);

        return Inertia::render('Teams/Edit', [
            'team' => $team = FindTeam::run($team->id, Organization::collect(Auth::user()->organizations)),
            'users' => GetTeamNonMembers::run($team),
            'members' => GetTeamMembers::run(
                $team,
                $request->get('orderBy', ['name']),
                $request->integer('page', null),
                $request->integer('perPage', 20),
                $request->get('filters', [])
            ),
            'page' => $request->integer('page', 1),
            'perPage' => $request->integer('perPage', 10),
            'filters' => $request->get('filters', []),
        ]);
    }

    public function update(Team $team): RedirectResponse
    {
        Gate::authorize('update', $team);

        $team = UpdateTeam::run($team);

        return redirect(route('teams.edit', ['team' => $team->id]))->withAlert('success', 'Team Updated Successfully.');
    }
}
