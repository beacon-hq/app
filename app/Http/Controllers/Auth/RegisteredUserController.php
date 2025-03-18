<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Values\Invite;
use Inertia\Inertia;
use Inertia\Response;
use Session;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        /** @var Invite $invite */
        $invite = Session::get('invite');

        return Inertia::render('Auth/Register', [
            'invite' => !$invite || $invite->expires_at->isAfter(now()) ? $invite : false ,
        ]);
    }
}
