<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\AccessTokens\ListAccessTokens;
use Inertia\Inertia;
use Inertia\Response;

class AccessTokenController
{
    public function index(): Response
    {
        return Inertia::render('AccessTokens/Index', [
            'settings' => [
                'tokens' => ListAccessTokens::run(),
            ],
        ]);
    }
}
