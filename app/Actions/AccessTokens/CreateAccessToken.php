<?php

declare(strict_types=1);

namespace App\Actions\AccessTokens;

use App\Actions\AppContext\GetAppContext;
use App\Actions\Traits\AsAction;
use App\Models\Team;
use App\Values\AccessToken;

/**
 * @method static AccessToken run(AccessToken $accessToken)
 */
class CreateAccessToken
{
    use AsAction;

    public function handle(AccessToken $accessToken): AccessToken
    {
        $team = Team::findOrFail(GetAppContext::team()?->id);
        $token = $team->createToken($accessToken->name);

        return AccessToken::from($token);
    }
}
