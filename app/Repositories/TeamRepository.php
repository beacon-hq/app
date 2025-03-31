<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Scopes\CurrentOrganizationScope;
use App\Models\Team;
use App\Services\UserService;
use App\Values\Collections\TeamCollection;
use App\Values\Collections\UserCollection;
use App\Values\Team as TeamValue;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamRepository
{
    public function __construct()
    {
    }

    public function findBySlug(string $slug): TeamValue
    {
        return TeamValue::from(
            Team::where('slug', $slug)
                ->firstOrFail()
        );
    }

    public function findById(string $id): TeamValue
    {
        return TeamValue::from(
            Team::query()
                ->withoutGlobalScopes([CurrentOrganizationScope::class])
                ->with(
                    'organization',
                    fn (BelongsTo $query) => $query
                        ->whereIn('id', Auth::user()->organizations()->pluck('id'))
                )
                ->findOrFail($id)
        );
    }

    public function all(?int $user = null, bool $includeMembers = false, bool $limitToOrganization = true): TeamCollection
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

        return TeamValue::collect(
            $query->get()
        );
    }

    public function create(TeamValue $team): TeamValue
    {
        return TeamValue::from(Team::create([
            'name' => $team->name,
            'color' => $team->color,
            'icon' => $team->icon,
        ]));
    }

    public function update(TeamValue $team): TeamValue
    {
        $teamModel = Team::withoutGlobalScopes([CurrentOrganizationScope::class])->findOrFail($team->id);
        $teamModel->update(
            $team
                ->toCollection()
                ->only(['name', 'color', 'icon'])
                ->toArray()
        );

        return $team->with(slug: $teamModel->slug);
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
