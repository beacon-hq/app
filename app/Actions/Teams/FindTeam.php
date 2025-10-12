<?php

declare(strict_types=1);

namespace App\Actions\Teams;

use App\Actions\Traits\AsAction;
use App\Models\Scopes\CurrentOrganizationScope;
use App\Models\Team as TeamModel;
use App\Values\Collections\OrganizationCollection;
use App\Values\Team;

/**
 * @method static Team run(string $id, OrganizationCollection $organizations)
 */
class FindTeam
{
    use AsAction;

    public function handle(string $id, OrganizationCollection $organizations): Team
    {
        $team = TeamModel::query()
            ->withoutGlobalScopes([CurrentOrganizationScope::class])
            ->whereIn('organization_id', $organizations->pluck('id')->toArray())
            ->findOrFail($id);

        return Team::from($team);
    }
}
