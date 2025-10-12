<?php

declare(strict_types=1);

namespace App\Actions\AccessTokens;

use App\Actions\AppContext\GetAppContext;
use App\Actions\Traits\AsAction;
use App\Models\Scopes\CurrentTeamScope;
use App\Models\Team;
use App\Values\AccessToken;

class DeleteAccessToken
{
    use AsAction;

    public function handle(AccessToken $accessToken): bool
    {
        /** @var Team $team */
        $team = Team::findOrFail(GetAppContext::team()->id);

        return $team
            ->tokens()
            ->withoutGlobalScope(CurrentTeamScope::class)
            ->whereMorphedTo('tokenable', $team)
            ->where('id', $accessToken->id)
            ->delete() !== 0;
    }
}
