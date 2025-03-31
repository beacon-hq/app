<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App;
use Auth;
use Closure;
use Illuminate\Http\Request;
use Session;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guest()) {
            return $next($request);
        }

        if (Session::has('team')) {
            $organization = Session::get('team')->organization;

            App::context(organization: $organization);
        }

        return $next($request);
    }
}
