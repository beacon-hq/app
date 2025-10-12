<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Actions\AppContext\SetOrganizationContext;
use App\Actions\AppContext\SetTeamContext;
use App\Models\Team;
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

            SetOrganizationContext::run($organization);
        }

        $team = Auth::user();
        if ($team instanceof Team) {
            SetTeamContext::run($team);
            SetOrganizationContext::run($team->organization);
        }

        return $next($request);
    }
}
