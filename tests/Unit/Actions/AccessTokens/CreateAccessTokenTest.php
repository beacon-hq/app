<?php

declare(strict_types=1);

use App\Actions\AccessTokens\CreateAccessToken;
use App\Values\AccessToken;
use Tests\Unit\Actions\AppContext\SetAppContext;

beforeEach(function () {
    [$this->team,, $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

    $this->createAccessTokenAction = CreateAccessToken::make();
});

it('creates a new access token', function () {
    $accessToken = AccessToken::from(name: 'Test Token');

    $result = $this->createAccessTokenAction->handle($accessToken);

    expect($result)->toBeInstanceOf(AccessToken::class)
        ->and($result->name)->toBe('Test Token')
        ->and($result->token)->toBeString()
        ->and(strlen($result->token))->toBeGreaterThan(0);

    // Verify the token exists in the database
    $this->assertDatabaseHas('access_tokens', [
        'name' => 'Test Token',
        'tokenable_id' => $this->team->id,
        'tokenable_type' => get_class($this->team),
    ]);
});
