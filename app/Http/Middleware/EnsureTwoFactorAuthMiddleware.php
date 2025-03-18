<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;
use Session;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guest()) {
            return $next($request);
        }

        if (Session::has('status') && Session::get('status') === 'two-factor-authentication-confirmed') {
            return redirect()->route('two-factor.confirmed')->with(
                'alert',
                [
                    'message' => 'Two factor successfully enabled.',
                    'status' => 'success',
                ]
            );
        }

        if ($request->routeIs('two-factor.*', 'password.*', 'verification.*', 'login.*')) {
            return $next($request);
        }

        if (Auth::user()->email_verified_at !== null && Auth::user()->two_factor_confirmed_at === null) {
            return redirect()->route('two-factor.setup');
        }

        return $next($request);
    }
}
