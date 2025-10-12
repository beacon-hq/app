<?php

declare(strict_types=1);

namespace App\Actions\AccessTokens;

use App\Actions\AppContext\GetAppContext;
use App\Actions\Traits\AsAction;
use App\Models\Team;
use App\Values\AccessToken;
use App\Values\Collections\AccessTokenCollection;

class ListAccessTokens
{
    use AsAction;

    public function handle(): AccessTokenCollection
    {
        $accessTokens = Team::findOrFail(GetAppContext::team()->id)->tokens;

        return AccessToken::collect($accessTokens);
    }
}
