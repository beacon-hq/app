<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App;
use App\Models\PersonalAccessToken;
use App\Models\Tenant;
use Inertia\Inertia;
use Inertia\Response;
use Str;

class SettingsController extends Controller
{
    public function index(): Response
    {
        $tokens = Tenant::findOrFail(App::context()->tenant->id)->tokens->map(fn (PersonalAccessToken $token) => [
            'id' => $token->id,
            'name' => $token->name,
            'token' => Str::of($token->plain_text_suffix)->prepend('*********************'),
            'last_used_at' => $token->last_used_at,
            'created_at' => $token->created_at,
        ]);

        return Inertia::render('Settings/APITokens/Index', [
            'settings' => [
                'tokens' => $tokens,
            ],
        ]);
    }
}
