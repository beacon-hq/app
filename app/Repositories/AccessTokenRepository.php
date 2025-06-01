<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\AccessToken;
use App\Models\Team;
use App\Values\AccessToken as AccessTokenValue;
use App\Values\Team as TeamValue;
use Illuminate\Database\Eloquent\Collection;

class AccessTokenRepository
{
    public function __construct()
    {
    }

    public function all(TeamValue $team): Collection
    {
        return Team::findOrFail($team->id)->tokens;
    }

    public function create(AccessTokenValue $accessToken, TeamValue $team): AccessToken
    {
        /** @var Team $team */
        $team = Team::findOrFail($team->id);

        $token = $team->createToken($accessToken->name);

        // Return the access token model
        return $token->accessToken;
    }

    public function delete(AccessTokenValue $accessToken, TeamValue $team): bool
    {
        /** @var Team $team */
        $team = Team::findOrFail($team->id);

        return $team->tokens()->where('id', $accessToken->id)->delete() !== 0;
    }


    public function findById(string $id): AccessToken
    {
        return AccessToken::findOrFail($id);
    }
}
