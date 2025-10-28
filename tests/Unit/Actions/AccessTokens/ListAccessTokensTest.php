<?php

declare(strict_types=1);

use App\Actions\AccessTokens\ListAccessTokens;
use App\Actions\AppContext\SetAppContext;
use App\Models\AccessToken as AccessTokenModel;

beforeEach(function () {
    [$this->team, $user, $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

    // Create access tokens for the team
    $this->accessTokenA = $this->team->createToken('token-a');
    $this->accessTokenB = $this->team->createToken('token-b');
    $this->accessTokenC = $this->team->createToken('token-c');

    $this->listAccessTokensAction = ListAccessTokens::make();
});

it('returns all access tokens for the team', function () {
    $result = $this->listAccessTokensAction->handle();

    expect($result)->toHaveCount(3);
});

it('returns an empty collection when no access tokens exist', function () {
    // Delete all access tokens
    AccessTokenModel::query()->delete();

    $result = $this->listAccessTokensAction->handle();

    expect($result)->toHaveCount(0)
        ->and($result)->toBeEmpty();
});
