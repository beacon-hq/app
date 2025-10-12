<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Values\Invite;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        /** @var Invite $invite */
        $invite = Session::get('invite');

        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => Session::get('status'),
            'invite' => !$invite || $invite->expires_at->isAfter(now()) ? $invite : false ,
        ]);
    }
}
