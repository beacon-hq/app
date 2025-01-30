<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AccessTokenService;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(AccessTokenService $accessTokenService): Response
    {
        return Inertia::render('Settings/APITokens/Index', [
            'settings' => [
                'tokens' => $accessTokenService->all(),
            ],
        ]);
    }
}
