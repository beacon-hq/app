<?php

declare(strict_types=1);

namespace App\Actions\Teams;

use App\Actions\Traits\AsAction;
use App\Actions\Users\AddUserToTeam;
use App\Models\Team as TeamModel;
use App\Values\Team;
use App\Values\User;

/**
 * @method static Team run(Team $team, User $owner)
 */
class CreateTeam
{
    use AsAction;

    public function handle(Team $team, User $owner): Team
    {
        $createdTeam = TeamModel::create([
            'name' => $team->name,
            'color' => $team->has('color') ? $team->color : null,
            'icon' => $team->has('icon') ? $team->icon : null,
        ]);

        $teamValue = Team::from($createdTeam);
        AddUserToTeam::run($owner, $teamValue);

        return $teamValue;
    }
}
