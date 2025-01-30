<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\PersonalAccessToken;
use App\Models\Team;
use App\Values\AccessToken as AccessTokenValue;
use App\Values\Collections\AccessTokenCollection;
use App\Values\Team as TeamValue;
use Illuminate\Support\Str;

class AccessTokenRepository
{
    public function __construct()
    {
    }

    public function all(TeamValue $team): AccessTokenCollection
    {
        return AccessTokenValue::collect(Team::findOrFail($team->id)->tokens);
    }

    public function create(AccessTokenValue $accessToken, TeamValue $team): AccessTokenValue
    {
        /** @var Team $team */
        $team = Team::findOrFail($team->id);

        $token = $team->createToken($accessToken->name);

        return AccessTokenValue::from(
            id: (int) Str::before($token->plainTextToken, '|'),
            name: $accessToken->name,
            token: Str::after($token->plainTextToken, '|'),
            lastUsedAt: null,
            createdAt: $token->accessToken->created_at
        );
    }

    public function delete(AccessTokenValue $accessToken, TeamValue $team): bool
    {
        /** @var Team $team */
        $team = Team::findOrFail($team->id);

        return $team->tokens()->where('id', $accessToken->id)->delete() !== 0;
    }


    public function findById(string $id): AccessTokenValue
    {
        return AccessTokenValue::from(PersonalAccessToken::findOrFail($id));
    }
}
