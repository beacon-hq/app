<?php

declare(strict_types=1);

namespace App\Actions\Invites;

use App\Actions\Traits\AsAction;
use App\Models\Invite as InviteModel;
use App\Values\Collections\InviteCollection;
use App\Values\Invite;
use App\Values\Team;

class FindInvitesByTeam
{
    use AsAction;

    public function handle(Team $team): InviteCollection
    {
        $invites = InviteModel::query()
            ->where('team_id', $team->id)
            ->get();

        return Invite::collect($invites);
    }
}
