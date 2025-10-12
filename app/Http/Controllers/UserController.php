<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\AppContext\GetAppContext;
use App\Actions\Invites\CreateInvite;
use App\Actions\Invites\ListInvites;
use App\Actions\Teams\ListTeams;
use App\Actions\Users\AssignRoleToUser;
use App\Actions\Users\DeleteUser;
use App\Actions\Users\ListUsers;
use App\Actions\Users\SyncUserTeams;
use App\Actions\Users\UpdateUser;
use App\Enums\Role;
use App\Enums\UserStatus;
use App\Values\Collections\TeamCollection;
use App\Values\Team;
use App\Values\User;
use Bag\Attributes\WithoutValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class UserController
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', User::class);

        return Inertia::render('Users/Index', [
            'users' => ListUsers::run(
                $request->get('orderBy', ['first_name', 'last_name']),
                $request->get('filters', []),
                $request->integer('page', 1),
                $request->integer('perPage', 20),
            ),
            'invites' => ListInvites::run(),
            'teams' => ListTeams::run(),
            'filters' => $request->get('filters', []),
            'page' => $request->integer('page', 1),
            'perPage' => $request->integer('perPage', 20),
        ]);
    }

    public function store(Request $request): HttpResponse|RedirectResponse
    {
        Gate::authorize('create', User::class);

        CreateInvite::run(
            Auth::user(),
            Team::from(id: $request->string('team')),
            GetAppContext::organization(),
            $request->input('email'),
            Role::tryFrom($request->input('role')),
        );

        return redirect()->route('users.index')
            ->withAlert('success', 'Invitation Sent Successfully.');
    }

    public function update(
        #[WithoutValidation]
        User $user,
        Request $request,
    ): RedirectResponse {
        Gate::authorize('update', $user);

        AssignRoleToUser::run($user, Role::tryFrom($request->input('role')));
        SyncUserTeams::run($user, TeamCollection::wrap(collect($request->input('teams'))->map(fn (array $team) => Team::from($team))));

        if ($request->has('status')) {
            // Update the status
            $user = $user->set('status', $request->boolean('status') ? UserStatus::ACTIVE : UserStatus::INACTIVE);
            UpdateUser::run($user);
        }

        return redirect()->back()
            ->withAlert('success', 'User Updated Successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        DeleteUser::run($user);

        return redirect()->route('users.index')
            ->withAlert('success', 'User Deleted Successfully.');
    }
}
