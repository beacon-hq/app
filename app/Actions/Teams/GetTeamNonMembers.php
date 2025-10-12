<?php

declare(strict_types=1);

namespace App\Actions\Teams;

use App\Actions\Traits\AsAction;
use App\Models\Scopes\CurrentTeamScope;
use App\Models\User;
use App\Values\Collections\UserCollection;
use App\Values\Team;
use App\Values\User as UserValue;
use Illuminate\Database\Eloquent\Builder;

class GetTeamNonMembers
{
    use AsAction;

    public function handle(Team $team): UserCollection
    {
        $users = User::withoutGlobalScopes([CurrentTeamScope::class])
            ->whereDoesntHave('teams', fn (Builder $query) => $query->where('id', $team->id))
            ->get();

        return UserValue::collect($users);
    }
}
