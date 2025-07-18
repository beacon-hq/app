<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Scopes\CurrentOrganizationScope;
use App\Models\Team;
use App\Services\UserService;
use App\Values\Collections\OrganizationCollection;
use App\Values\Collections\UserCollection;
use App\Values\Team as TeamValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamRepository
{
    public function find(string $id, OrganizationCollection $organizations): Team
    {
        return Team::query()
            ->withoutGlobalScopes([CurrentOrganizationScope::class])
            ->withWhereHas(
                'organization',
                fn (BelongsTo|Builder $query) => $query
                    ->whereIn('id', $organizations->pluck('id')->toArray())
            )
            ->findOrFail($id);
    }

    public function all(?int $user = null, bool $includeMembers = false, bool $limitToOrganization = true): Collection
    {
        $query = Team::query()
            ->when(
                $user !== null,
                fn (Builder $query) => $query->whereHas('users', fn (Builder $query) => $query->withoutGlobalScopes([CurrentOrganizationScope::class])->where('id', $user))
            )
            ->when(!$limitToOrganization, fn (Builder $query) => $query->withoutGlobalScopes([CurrentOrganizationScope::class]))
            ->when($includeMembers, fn (Builder $query) => $query->with('users'))
            ->orderBy('organization_id')
            ->orderBy('name');

        return $query->get();
    }

    public function create(TeamValue $team): Team
    {
        return Team::create([
            'name' => $team->name,
            'color' => $team->has('color') ? $team->color : null,
            'icon' => $team->has('icon') ? $team->icon : null,
        ]);
    }

    public function update(TeamValue $team): Team
    {
        $teamModel = Team::withoutGlobalScopes([CurrentOrganizationScope::class])->findOrFail($team->id);
        $teamModel->update(
            $team
                ->toCollection()
                ->only(['name', 'color', 'icon'])
                ->toArray()
        );

        return $teamModel->fresh();
    }

    public function members(TeamValue $team, array|string $orderBy = ['name'], ?int $page = null, int $perPage = 20, array $filters = []): UserCollection
    {
        $userService = resolve(UserService::class);

        return $userService->teamMembers($team, $orderBy, $page, $perPage, $filters);
    }

    public function nonMembers(TeamValue $team): UserCollection
    {
        $userService = resolve(UserService::class);

        return $userService->nonTeamMembers($team);
    }
}
