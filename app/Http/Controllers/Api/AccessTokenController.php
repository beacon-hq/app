<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\AccessTokens\CreateAccessToken;
use App\Actions\AccessTokens\DeleteAccessToken;
use App\Actions\AccessTokens\ListAccessTokens;
use App\Values\AccessToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class AccessTokenController
{
    public function store(AccessToken $accessToken): JsonResponse
    {
        Gate::authorize('create', $accessToken);

        return response()->json(CreateAccessToken::run($accessToken));
    }

    public function show(): JsonResponse
    {
        Gate::authorize('viewAny', AccessToken::class);

        return response()->json(ListAccessTokens::run());
    }

    public function destroy(
        AccessToken $accessToken,
    ): Response {
        Gate::authorize('delete', $accessToken);

        if (DeleteAccessToken::run($accessToken)) {
            return response(null, 204);
        }

        abort(500, 'Failed to delete access token.');
    }
}
