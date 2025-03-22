<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AccessTokenService;
use Inertia\Inertia;
use Inertia\Response;

class AccessTokenController extends Controller
{
    public function index(AccessTokenService $accessTokenService): Response
    {
        return Inertia::render('AccessTokens/Index', [
            'settings' => [
                'tokens' => $accessTokenService->all(),
            ],
        ]);
    }
}
