<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureOrganizationMiddleware;
use App\Http\Middleware\EnsureTeamMiddleware;
use App\Http\Middleware\EnsureTwoFactorAuthMiddleware;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Routing\Middleware\SubstituteBindings;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            \Route::prefix('api')
                ->middleware('api')
                ->name('api.')
                ->group(base_path('routes/api.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            EnsureTwoFactorAuthMiddleware::class,
            EnsureTeamMiddleware::class,
            EnsureOrganizationMiddleware::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->statefulApi();

        $middleware->api(append: [
            'auth:sanctum',
            EnsureTeamMiddleware::class,
        ]);

        $middleware->prependToPriorityList(SubstituteBindings::class, EnsureOrganizationMiddleware::class);
        $middleware->prependToPriorityList(SubstituteBindings::class, EnsureTeamMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
