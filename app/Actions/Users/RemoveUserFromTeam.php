<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Actions\Invites\DeleteInvite;
use App\Actions\Invites\FindTeamInvite;
use App\Actions\Traits\AsAction;
use App\Models\User;
use App\Values\Team;
use App\Values\User as UserValue;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RemoveUserFromTeam
{
    use AsAction;

    public function handle(Team $team, UserValue $user): void
    {
        try {
            if ($user->has('email') && $invite = FindTeamInvite::run($team, $user->email)) {
                DeleteInvite::run($invite);
            }
        } catch (ModelNotFoundException) {
            // Ignore if invite doesn't exist
        }

        if ($user->id !== null) {
            $userModel = User::findOrFail($user->id);
            $userModel->teams()->detach($team->id);
        }
    }
}
