<?php

declare(strict_types=1);

namespace App\Actions\AccessTokens;

use App\Actions\Traits\AsAction;
use App\Models\AccessToken as AccessTokenModel;
use App\Values\AccessToken;

class FindAccessTokenById
{
    use AsAction;

    public function handle(string $id): AccessToken
    {
        $accessToken = AccessTokenModel::findOrFail($id);

        return AccessToken::from($accessToken);
    }
}
