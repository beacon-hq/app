<?php

declare(strict_types=1);

namespace App\Actions\Onboarding;

use App\Actions\AccessTokens\CreateAccessToken;
use App\Actions\Traits\AsAction;
use App\Models\AccessToken;
use App\Values\AccessToken as AccessTokenValue;

class GetAccessToken
{
    use AsAction;

    public function handle(): string
    {
        if ($accessToken = AccessToken::first()) {
            return $accessToken->token_value;
        }

        return CreateAccessToken::run(AccessTokenValue::from(name: 'Beacon Onboarding'))->token;
    }
}
