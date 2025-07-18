<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\InviteService;
use App\Services\TeamService;
use App\Services\UserService;
use App\Values\Organization;
use App\Values\Team;
use App\Values\User;
use Bag\Attributes\WithoutValidation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;

class TeamMemberManageController extends Controller
{
    public function store(
        #[WithoutValidation]
        Team $team,
        Request $request,
        UserService $userService,
        TeamService $teamService,
    ): RedirectResponse {
        Gate::authorize('update', $team);

        $request->validate([
            'users' => 'nullable|array',
            'users.*.email' => 'required_with:emails|email',
        ]);

        $team = $teamService->find($team->id, Organization::collect(Auth::user()->organizations));

        collect($request->json('users'))->each(function (array $user) use ($team, $userService) {
            // $role = Role::tryFrom($user['role']);

            try {
                $user = $userService->findByEmail($user['email']);
                $userService->addTeam($user, $team);
                // $userService->assignRole($user, $role);
            } catch (ModelNotFoundException) {
                // Unknown user, send an invite
                // $inviteService->create(User::from(Auth::user()), $team, $user['email']);
            }
        });

        return \redirect()->route('teams.edit', ['team' => $team->id])->withAlert('success', 'Team members added successfully.');
    }

    public function show(Request $request, InviteService $inviteService, UserService $userService): RedirectResponse
    {
        try {
            $invite = $inviteService->findById($request->get('id'));
        } catch (ModelNotFoundException) {
            $invite = false;
        }

        Session::put('invite', $invite);

        try {
            $userService->findByEmail($invite->email);

            return redirect()->route('login');
        } catch (ModelNotFoundException) {
            return redirect()->route('register');
        }
    }

    public function destroy(
        #[WithoutValidation]
        Team $team,
        Request $request,
        UserService $userService
    ): RedirectResponse {
        $user = User::withoutValidation(id: $request->integer('user_id') ?: null, email: $request->get('email'));
        $userService->removeTeam($team, $user);

        return redirect()->back()->withAlert('success', 'Team member removed successfully.');
    }
}
