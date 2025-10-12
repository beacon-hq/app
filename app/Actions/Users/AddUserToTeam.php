<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Actions\Teams\CreateTeam;
use App\Actions\Teams\FindTeam;
use App\Actions\Traits\AsAction;
use App\Models\Scopes\CurrentTeamScope;
use App\Models\User;
use App\Values\Organization;
use App\Values\Team;
use App\Values\User as UserValue;
use Bag\Values\Optional;
use Illuminate\Support\Facades\Auth;

class AddUserToTeam
{
    use AsAction;

    public function handle(UserValue $user, Team $team): UserValue
    {
        if ($team->id !== null && !($team->id instanceof Optional)) {
            $team = FindTeam::run($team->id, Organization::collect(Auth::user()->organizations));
        } else {
            $team = CreateTeam::run($team, $user);
        }

        $userModel = User::withoutGlobalScopes([CurrentTeamScope::class])->findOrFail($user->id);
        $userModel->teams()->syncWithoutDetaching($team->id);

        return UserValue::from($userModel);
    }
}
