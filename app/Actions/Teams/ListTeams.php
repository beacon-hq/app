<?php

declare(strict_types=1);

namespace App\Actions\Teams;

use App\Actions\Traits\AsAction;
use App\Models\Scopes\CurrentOrganizationScope;
use App\Models\Team as TeamModel;
use App\Values\Collections\TeamCollection;
use App\Values\Team;
use Illuminate\Database\Eloquent\Builder;

class ListTeams
{
    use AsAction;

    public function handle(?int $userId = null, bool $includeMembers = false, bool $limitToOrganization = true): TeamCollection
    {
        $query = TeamModel::query()
            ->when(
                $userId !== null,
                fn (Builder $query) => $query->whereHas('users', fn (Builder $query) => $query->withoutGlobalScopes([CurrentOrganizationScope::class])->where('id', $userId))
            )
            ->when(!$limitToOrganization, fn (Builder $query) => $query->withoutGlobalScopes([CurrentOrganizationScope::class]))
            ->when($includeMembers, fn (Builder $query) => $query->with('users'))
            ->orderBy('organization_id')
            ->orderBy('name');

        $teams = $query->get();

        return Team::collect($teams);
    }
}
