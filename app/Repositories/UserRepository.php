<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\Role;
use App\Models\Scopes\CurrentOrganizationScope;
use App\Models\Scopes\CurrentTeamScope;
use App\Models\User;
use App\Services\TeamService;
use App\Values\Collections\TeamCollection;
use App\Values\Organization;
use App\Values\Team;
use App\Values\User as UserValue;
use Bag\Values\Optional;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserRepository
{
    public function __construct(protected TeamService $teamService)
    {
    }

    public function addTeam(UserValue $user, Team $team): User
    {
        if ($team->id !== null && !($team->id instanceof Optional)) {
            $team = $this->teamService->find($team->id, Organization::collect(Auth::user()->organizations));
        } else {
            $team = $this->teamService->create($team, $user);
        }

        $user = User::withoutGlobalScopes([CurrentTeamScope::class])->find($user->id);
        $user->teams()->syncWithoutDetaching($team->id);

        return $user;
    }

    public function removeTeam(Team $team, UserValue $user): User
    {
        $user = User::findOrFail($user->id);
        $user->teams()->detach($team->id);

        return $user;
    }

    public function findByEmail(string $email): User
    {
        return User::withoutGlobalScopes([CurrentTeamScope::class])->where('email', $email)->firstOrFail();
    }

    public function addOrganization(UserValue $user, Organization $organization): User
    {
        $user = User::withoutGlobalScopes([CurrentOrganizationScope::class, CurrentTeamScope::class])->findOrFail($user->id);
        $user->organizations()->syncWithoutDetaching($organization->id);

        return $user;
    }

    public function assignRole(UserValue $user, Role $role): User
    {
        $user = User::withoutGlobalScopes([CurrentTeamScope::class])->findOrFail($user->id);
        $user->syncRoles($role);

        return $user;
    }

    public function teamMembers(Team $team, array|string $orderBy = ['name'], ?int $page = null, ?int $perPage = null, array $filters = []): Collection
    {
        $filters['teams'][] = $team->id;

        return $this->buildQuery(orderBy: $orderBy, filters: $filters, page: $page, perPage: $perPage)->get();
    }

    public function nonTeamMembers(Team $team): Collection
    {
        return User::withoutGlobalScopes([CurrentTeamScope::class])
            ->whereDoesntHave('teams', fn (Builder $query) => $query->where('id', $team->id))
            ->get();
    }

    public function create(UserValue $user): User
    {
        $user = User::create($user->toArray());

        Auth::login($user);

        return $user;
    }

    public function all(array $orderBy = ['first_name', 'last_name'], array $filters = [], ?int $page = null, int $perPage = 20): Collection
    {
        return $this->buildQuery(orderBy: $orderBy, filters: $filters, page: $page, perPage: $perPage)
            ->with('teams')
            ->withoutGlobalScopes([CurrentTeamScope::class])
            ->get();
    }

    public function syncTeams(UserValue $user, TeamCollection $teams): User
    {
        $user = User::withoutGlobalScopes([CurrentTeamScope::class])->find($user->id);
        $user->teams()->sync($teams->pluck('id')->toArray());

        return $user;
    }

    public function delete(UserValue $user): void
    {
        $user = User::withoutGlobalScopes([CurrentTeamScope::class])->find($user->id);

        // Remove Roles
        $user->syncRoles([]);


        $user->delete();
    }

    public function update(UserValue $user): User
    {
        $userModel = User::withoutGlobalScopes([CurrentTeamScope::class])->findOrFail($user->id);
        $userModel->update($user->toCollection()->only('status')->toArray());

        return $userModel;
    }

    public function find(int $id): User
    {
        return User::withoutGlobalScopes([CurrentTeamScope::class])->findOrFail($id);
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
