<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\TeamService;
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

class TeamController extends Controller
{
    public function __construct(protected TeamService $teamService)
    {
    }

    public function index(): Response
    {
        Gate::authorize('viewAny', Team::class);

        return Inertia::render('Teams/Index', [
            'teams' => $this->teamService->all(Auth::user()->id, true),
        ]);
    }

    public function store(Team $team): RedirectResponse
    {
        Gate::authorize('create', Team::class);

        $team = $this->teamService->create($team, User::from(Auth::user()));

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
            'team' => $team = $this->teamService->find($team->id, Organization::collect(Auth::user()->organizations)),
            'users' => $this->teamService->nonMembers($team),
            'members' => $this->teamService->members(
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

        $team = $this->teamService->update($team);

        return redirect(route('teams.edit', ['team' => $team->id]))->withAlert('success', 'Team Updated Successfully.');
    }
}
