<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App;
use App\Models\Team;
use Auth;
use Closure;
use Illuminate\Http\Request;
use Session;
use Symfony\Component\HttpFoundation\Response;

class EnsureTeamMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guest()) {
            return $next($request);
        }

        if (!Session::has('team') || Session::get('team') === null) {
            if (Auth::user() instanceof Team) {
                Session::put('team', Auth::user());
            } else {
                if (Auth::user()->teams()->count() > 1 && !$request->routeIs('teams.select')) {
                    return redirect()->route('teams.select');
                }

                $team = Auth::user()->teams()->first();
                Session::put('team', $team);
            }
        }

        $team = Session::get('team');

        App::context(team: $team);

        return $next($request);
    }
}
