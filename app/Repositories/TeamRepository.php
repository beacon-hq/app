<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Team;
use App\Values\Collections\TeamCollection;
use App\Values\Team as TeamValue;
use Illuminate\Database\Eloquent\Builder;

class TeamRepository
{
    public function findBySlug(string $slug, bool $includeMembers = false): TeamValue
    {
        return TeamValue::from(
            Team::where('slug', $slug)
                ->when($includeMembers, function (Builder $query) {
                    $query->with('users');
                })
                ->firstOrFail()
        );
    }

    public function findById(string $id, bool $includeMembers = false): TeamValue
    {
        return TeamValue::from(
            Team::query()
            ->when($includeMembers, function (Builder $query) {
                $query->with('users');
            })
            ->findOrFail($id)
        );
    }

    public function all(?int $user = null, bool $includeMembers = false): TeamCollection
    {
        return TeamValue::collect(
            Team::query()
                ->when(
                    $user !== null,
                    fn (Builder $query) =>
                        $query->whereHas('users', fn (Builder $query) => $query->where('id', $user))
                )
                ->when($includeMembers, fn (Builder $query) => $query->with('users'))
                ->orderBy('name')->get()
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
}
