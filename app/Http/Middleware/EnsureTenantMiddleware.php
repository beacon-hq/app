<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App;
use App\Models\Tenant;
use Auth;
use Closure;
use Illuminate\Http\Request;
use Session;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guest()) {
            return $next($request);
        }

        if (!Session::has('tenant')) {
            if (Auth::user() instanceof Tenant) {
                Session::put('tenant', Auth::user());
            } else {
                $tenant = Auth::user()->tenants()->orderBy('created_at')->first();
                Session::put('tenant', $tenant);
            }
        }

        $tenant = Session::get('tenant');

        App::context(tenant: $tenant);

        return $next($request);
    }
}
