<?php

declare(strict_types=1);

namespace App\Actions\Invites;

use App\Actions\Traits\AsAction;
use App\Models\Invite as InviteModel;
use App\Models\Scopes\CurrentTeamScope;
use App\Values\Invite;
use App\Values\Team;

class FindTeamInvite
{
    use AsAction;

    public function handle(Team $team, string $email): Invite
    {
        $invite = InviteModel::query()
            ->withoutGlobalScope(CurrentTeamScope::class)
            ->where('team_id', $team->id)
            ->where('email', $email)
            ->firstOrFail();

        return Invite::from($invite);
    }
}
