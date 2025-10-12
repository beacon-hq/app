<?php

declare(strict_types=1);

namespace App\Actions\Teams;

use App\Actions\Traits\AsAction;
use App\Models\Scopes\CurrentOrganizationScope;
use App\Models\Team as TeamModel;
use App\Values\Team;

class UpdateTeam
{
    use AsAction;

    public function handle(Team $team): Team
    {
        $teamModel = TeamModel::withoutGlobalScopes([CurrentOrganizationScope::class])->findOrFail($team->id);
        $teamModel->update(
            $team
                ->toCollection()
                ->only(['name', 'color', 'icon'])
                ->toArray()
        );

        return Team::from($teamModel->fresh());
    }
}
