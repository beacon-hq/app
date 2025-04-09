<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\Role;
use App\Models\Scopes\CurrentOrganizationScope;
use App\Models\Scopes\CurrentTeamScope;
use App\Models\User;
use App\Services\TeamService;
use App\Values\Collections\TeamCollection;
use App\Values\Collections\UserCollection;
use App\Values\Organization;
use App\Values\Team;
use App\Values\User as UserValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserRepository
{
    public function __construct(protected TeamService $teamService)
    {
    }

    public function addTeam(UserValue $user, Team $team): UserValue
    {
        if ($team->id !== null) {
            $team = $this->teamService->find($team->id, Organization::collect(Auth::user()->organizations));
        } else {
            $team = $this->teamService->create($team, $user);
        }

        $user = User::withoutGlobalScopes([CurrentTeamScope::class])->find($user->id);
        $user->teams()->syncWithoutDetaching($team->id);

        return UserValue::from($user);
    }

    public function removeTeam(Team $team, UserValue $user): UserValue
    {
        $user = User::findOrFail($user->id);
        $user->teams()->detach($team->id);

        return UserValue::from($user);
    }

    public function findByEmail(string $email): UserValue
    {
        return UserValue::from(User::withoutGlobalScopes([CurrentTeamScope::class])->where('email', $email)->firstOrFail());
    }

    public function addOrganization(UserValue $user, Organization $organization): UserValue
    {
        $user = User::withoutGlobalScopes([CurrentOrganizationScope::class, CurrentTeamScope::class])->findOrFail($user->id);
        $user->organizations()->syncWithoutDetaching($organization->id);

        return UserValue::from($user);
    }

    public function assignRole(UserValue $user, Role $role): UserValue
    {
        $user = User::withoutGlobalScopes([CurrentTeamScope::class])->findOrFail($user->id);
        $user->syncRoles($role);

        return UserValue::from($user);
    }

    public function teamMembers(Team $team, array|string $orderBy = ['name'], ?int $page = null, int $perPage = null, array $filters = []): UserCollection
    {
        $filters['teams'][] = $team->id;

        return UserValue::collect($this->buildQuery(orderBy: $orderBy, filters: $filters, page: $page, perPage: $perPage)->get());
    }

    public function nonTeamMembers(Team $team): UserCollection
    {
        return UserValue::collect(User::withoutGlobalScopes([CurrentTeamScope::class])
            ->whereDoesntHave('teams', fn (Builder $query) => $query->where('id', $team->id))
            ->get());
    }

    public function create(UserValue $user): UserValue
    {
        $user = User::create($user->toArray());

        Auth::login($user);

        return UserValue::from($user);
    }

    public function all(array $orderBy = ['first_name', 'last_name'], array $filters = [], ?int $page = null, int $perPage = 20): UserCollection
    {
        return UserValue::collect(
            $this->buildQuery(orderBy: $orderBy, filters: $filters, page: $page, perPage: $perPage)
                ->with('teams')
                ->withoutGlobalScopes([CurrentTeamScope::class])
                ->get()
        );
    }

    public function syncTeams(UserValue $user, TeamCollection $teams): UserValue
    {
        $user = User::withoutGlobalScopes([CurrentTeamScope::class])->find($user->id);
        $user->teams()->sync($teams->pluck('id')->toArray());

        return UserValue::from($user);
    }

    public function delete(UserValue $user): void
    {
        $user = User::withoutGlobalScopes([CurrentTeamScope::class])->find($user->id);

        // Remove Roles
        $user->syncRoles([]);


        $user->delete();
    }

    public function update(UserValue $user)
    {
        $userModel = User::withoutGlobalScopes([CurrentTeamScope::class])->findOrFail($user->id);
        $userModel->update($user->toCollection()->only('status')->toArray());

        return UserValue::from($userModel);
    }

    public function find(int $id): UserValue
    {
        return UserValue::from(User::withoutGlobalScopes([CurrentTeamScope::class])->findOrFail($id));
    }

    public function sendEmailVerificationNotification(int $id): void
    {
        User::withoutGlobalScopes([CurrentTeamScope::class])->findOrFail($id)->sendEmailVerificationNotification();
    }

    /**
     * @param Builder<User> $query
     */
    protected function filter(Builder $query, array $filters = []): Builder
    {
        $query
            ->when(isset($filters['teams'][0]), fn (Builder $query) => $query->whereHas(
                'teams',
                fn (Builder $query) => $query->where(
                    fn (Builder $query) => $query
                        ->whereIn('id', $filters['teams'])
                        ->orWhereIn(
                            'name',
                            $filters['teams']
                        )
                )
            ))
            ->when(isset($filters['name'][0]), fn (Builder $query) => $query->where(fn (Builder $query) => $query->where(DB::raw('CONCAT(first_name, \' \', last_name)'), 'ILIKE', '%' . $filters['name'][0] . '%')->orWhere('email', 'ILIKE', '%' . $filters['name'][0] . '%')))
            ->when(isset($filters['roles']), fn (Builder $query) => $query->role($filters['roles']))
            ->when(isset($filters['status']), fn (Builder $query) => $query->whereIn('status', $filters['status']));

        return $query;
    }

    protected function buildQuery(array|string|null $orderBy = null, array $filters = [], ?int $page = null, int $perPage = 20): Builder
    {
        $query = User::query();

        foreach (Arr::wrap($orderBy) as $column) {
            if ($column === 'name') {
                $query = $query->orderBy('first_name')->orderBy('last_name');

                continue;
            }
            $query = $query->orderBy($column);
        }

        if (!empty($filters)) {
            $query = $this->filter($query, $filters);
        }

        if ($page !== null) {
            $query = $query->forPage($page, $perPage);
        }

        return $query;
    }
}
