<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AccessTokenService;
use App\Values\AccessToken;
use Bag\Attributes\WithoutValidation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AccessTokenController extends Controller
{
    public function store(AccessToken $accessToken, AccessTokenService $accessTokenService): JsonResponse
    {
        return response()->json($accessTokenService->create($accessToken));
    }

    public function show(AccessTokenService $accessTokenService): JsonResponse
    {
        return response()->json($accessTokenService->all());
    }

    public function destroy(
        #[WithoutValidation]
        AccessToken $accessToken,
        AccessTokenService $accessTokenService
    ): Response {
        if ($accessTokenService->delete($accessToken)) {
            return response(null, 204);
        }

        abort(500, 'Failed to delete access token.');
    }
}
