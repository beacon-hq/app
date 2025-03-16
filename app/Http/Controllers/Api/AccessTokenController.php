<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AccessTokenService;
use App\Values\AccessToken;
use Bag\Attributes\WithoutValidation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class AccessTokenController extends Controller
{
    public function store(AccessToken $accessToken, AccessTokenService $accessTokenService): JsonResponse
    {
        Gate::authorize('update', $accessToken);

        return response()->json($accessTokenService->create($accessToken));
    }

    public function show(AccessTokenService $accessTokenService): JsonResponse
    {
        Gate::authorize('viewAny', AccessToken::class);

        return response()->json($accessTokenService->all());
    }

    public function destroy(
        #[WithoutValidation]
        AccessToken $accessToken,
        AccessTokenService $accessTokenService
    ): Response {
        Gate::authorize('delete', $accessToken);

        if ($accessTokenService->delete($accessToken)) {
            return response(null, 204);
        }

        abort(500, 'Failed to delete access token.');
    }
}
