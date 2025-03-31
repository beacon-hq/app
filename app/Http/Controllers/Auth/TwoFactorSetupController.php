<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Auth;
use function decrypt;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;

class TwoFactorSetupController extends Controller
{
    public function index(): Response
    {
        $enable = resolve(EnableTwoFactorAuthentication::class);
        $enable(Auth::user(), false);

        return Inertia::render('Auth/TwoFactorAuth/Index', [
            'qrCode' => Auth::user()->twoFactorQrCodeSvg(),
            'secret' => decrypt(Auth::user()->two_factor_secret),
        ]);
    }

    public function show(): Response
    {
        return Inertia::render('Auth/TwoFactorAuth/Login');
    }
}
