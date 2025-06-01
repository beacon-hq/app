<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AccessTokenRepository;
use App\Values\AccessToken;
use App\Values\AppContext;
use App\Values\Collections\AccessTokenCollection;

class AccessTokenService
{
    public function __construct(protected AccessTokenRepository $accessTokenRepository, protected AppContext $appContext)
    {
    }

    public function all(): AccessTokenCollection
    {
        $accessTokens = $this->accessTokenRepository->all($this->appContext->team);

        return AccessToken::collect($accessTokens);
    }

    public function create(AccessToken $accessToken): AccessToken
    {
        $createdAccessToken = $this->accessTokenRepository->create($accessToken, $this->appContext->team);

        return AccessToken::from($createdAccessToken);
    }

    public function delete(AccessToken $accessToken): bool
    {
        return $this->accessTokenRepository->delete($accessToken, $this->appContext->team);
    }

    public function findById(string $id): AccessToken
    {
        $accessToken = $this->accessTokenRepository->findById($id);

        return AccessToken::from($accessToken);
    }
}
