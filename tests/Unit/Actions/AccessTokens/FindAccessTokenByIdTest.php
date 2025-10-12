<?php

declare(strict_types=1);

use App\Actions\AccessTokens\FindAccessTokenById;
use App\Models\AccessToken;
use App\Values\AccessToken as AccessTokenValue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\Unit\Actions\AppContext\SetAppContext;

beforeEach(function () {
    [$this->team, , $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

    // Create an access token for the team
    $this->newAccessToken = $this->team->createToken('Test Token');

    // Get the database record of the token
    $this->tokenModel = AccessToken::findOrFail($this->newAccessToken->accessToken->id);

    $this->findAccessTokenByIdAction = FindAccessTokenById::make();
});

it('finds an access token by ID', function () {
    $result = $this->findAccessTokenByIdAction->handle((string) $this->tokenModel->id);

    expect($result)->toBeInstanceOf(AccessTokenValue::class)
        ->and($result->id)->toBe($this->tokenModel->id)
        ->and($result->name)->toBe('Test Token');
});

it('throws an exception when token is not found', function () {
    $this->expectException(ModelNotFoundException::class);

    $this->findAccessTokenByIdAction->handle('99999');
});
