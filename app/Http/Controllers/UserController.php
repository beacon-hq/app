<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Services\InviteService;
use App\Services\TeamService;
use App\Services\UserService;
use App\Values\Collections\TeamCollection;
use App\Values\Team;
use App\Values\User;
use Bag\Attributes\WithoutValidation;
use Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService,
        protected TeamService $teamService,
        protected InviteService $inviteService,
    ) {
    }

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', User::class);

        return Inertia::render('Users/Index', [
            'users' => $this->userService->all(
                $request->get('orderBy', ['first_name', 'last_name']),
                $request->get('filters', []),
                $request->integer('page', 1),
                $request->integer('perPage', 20),
            ),
            'teams' => $this->teamService->all(),
            'filters' => $request->get('filters', []),
            'page' => $request->integer('page', 1),
            'perPage' => $request->integer('perPage', 20),
        ]);
    }

    public function store(Request $request, Team $team): RedirectResponse
    {
        Gate::authorize('create', User::class);

        $this->inviteService->create(
            User::from(Auth::user()),
            $team,
            $request->input('email'),
            $request->input('role'),
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

        $this->userService->assignRole($user, Role::tryFrom($request->input('role')));
        $this->userService->syncTeams($user, TeamCollection::wrap(collect($request->input('teams'))->map(fn (array $team) => Team::from($team))));

        if ($request->has('status')) {
            $this->userService->update($user, ['status' => $request->boolean('status') ? UserStatus::ACTIVE : UserStatus::INACTIVE]);
        }

        return redirect()->back()
            ->withAlert('success', 'User Updated Successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        $this->userService->delete($user);

        return redirect()->route('users.index')
            ->withAlert('success', 'User Deleted Successfully.');
    }
}
