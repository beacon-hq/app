<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Auth;
use Inertia\Inertia;

class TwoFactorConfirmedController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function show()
    {
        return Inertia::render('Auth/TwoFactorAuth/Confirmed', [
            'recoveryCodes' => Auth::user()->recoveryCodes(),
        ]);
    }
}
