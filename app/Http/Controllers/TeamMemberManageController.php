<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Invites\FindInviteById;
use App\Actions\Teams\FindTeam;
use App\Actions\Users\AddUserToTeam;
use App\Actions\Users\FindUserByEmail;
use App\Actions\Users\RemoveUserFromTeam;
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

class TeamMemberManageController
{
    public function store(
        #[WithoutValidation]
        Team $team,
        Request $request,
    ): RedirectResponse {
        Gate::authorize('update', $team);

        $request->validate([
            'users' => 'nullable|array',
            'users.*.email' => 'required_with:emails|email',
        ]);

        $team = FindTeam::run($team->id, Organization::collect(Auth::user()->organizations));

        collect($request->json('users'))->each(function (array $user) use ($team) {
            // $role = Role::tryFrom($user['role']);

            try {
                $user = FindUserByEmail::run($user['email']);
                AddUserToTeam::run($user, $team);
                // AssignRoleToUser::run($user, $role);
            } catch (ModelNotFoundException) {
                // Unknown user, send an invite
                // $inviteService->create(User::from(Auth::user()), $team, $user['email']);
            }
        });

        return \redirect()->route('teams.edit', ['team' => $team->id])->withAlert('success', 'Team members added successfully.');
    }

    public function show(Request $request): RedirectResponse
    {
        try {
            $invite = FindInviteById::run($request->get('id'));

            Session::put('invite', $invite);

            FindUserByEmail::run($invite->email);

            return redirect()->route('login');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('register');
        }
    }

    public function destroy(
        #[WithoutValidation]
        Team $team,
        Request $request
    ): RedirectResponse {
        $user = User::withoutValidation(id: $request->integer('user_id') ?: null, email: $request->get('email'));
        RemoveUserFromTeam::run($team, $user);

        return redirect()->back()->withAlert('success', 'Team member removed successfully.');
    }
}
