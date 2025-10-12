<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use Auth;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorConfirmedController
{
    /**
     * Display a listing of the resource.
     */
    public function show(): Response
    {
        return Inertia::render('Auth/TwoFactorAuth/Confirmed', [
            'recoveryCodes' => Auth::user()->recoveryCodes(),
        ]);
    }
}
