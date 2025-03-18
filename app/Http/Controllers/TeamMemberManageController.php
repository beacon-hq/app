<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Services\InviteService;
use App\Services\TeamService;
use App\Services\UserService;
use App\Values\Team;
use App\Values\User;
use Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rules\Enum;

class TeamMemberManageController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Team $team, Request $request, UserService $userService, TeamService $teamService, InviteService $inviteService)
    {
        $request->validate([
            'users' => 'nullable|array',
            'users.*.email' => 'required_with:emails|email',
            'users.*.role' => ['required', new Enum(Role::class)],
        ]);

        $team = $teamService->findBySlug($team->slug);

        collect($request->json('users'))->each(function (array $user) use ($team, $inviteService, $userService) {
            $role = Role::tryFrom($user['role']);

            try {
                $user = $userService->findByEmail($user['email']);
                $userService->addTeam($user, $team);
                $userService->assignRole($user, $role);
            } catch (ModelNotFoundException) {
                // Unknown user, send an invite
                $inviteService->create(User::from(Auth::user()), $team, $user['email'], $role);
            }
        });

        return \redirect()->route('teams.edit', ['slug' => $team->slug])->withAlert('success', 'Team members invited successfully.');
    }

    public function show(Request $request, InviteService $inviteService, UserService $userService)
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
}
