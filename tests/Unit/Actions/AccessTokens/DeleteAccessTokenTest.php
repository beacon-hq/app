<?php

declare(strict_types=1);

use App\Actions\AccessTokens\CreateAccessToken;
use App\Actions\AccessTokens\DeleteAccessToken;
use App\Values\AccessToken;
use Tests\Unit\Actions\AppContext\SetAppContext;

beforeEach(function () {
    [$this->team, , $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

    $this->accessToken = CreateAccessToken::run(AccessToken::from(name: 'Test Token'));

    $this->deleteAccessTokenAction = DeleteAccessToken::make();
});

it('deletes an access token', function () {
    $result = $this->deleteAccessTokenAction->handle($this->accessToken);

    expect($result)->toBeTrue();

    // Verify the token no longer exists in the database
    $this->assertDatabaseMissing('access_tokens', [
        'id' => $this->accessToken->id,
    ]);
});

it('returns false when trying to delete a non-existent token', function () {
    // Create a token value with a non-existent ID
    $nonExistentToken = AccessToken::from(id: 99999, name: 'Non-existent Token');

    $result = $this->deleteAccessTokenAction->handle($nonExistentToken);

    expect($result)->toBeFalse();
});
