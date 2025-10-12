<?php

declare(strict_types=1);

arch()
    ->expect('App\Models')
    ->classes()
    ->toOnlyBeUsedIn([
        'App\Actions',
        'App\Console\Commands',
        'App\Events',
        'App\Http\Controllers\Auth',
        'App\Http\Middleware\EnsureOrganizationMiddleware',
        'App\Http\Middleware\EnsureTeamMiddleware',
        'App\Http\Requests\ProfileUpdateRequest',
        'App\Listeners',
        'App\Models',
        'App\Policies',
        'App\Providers',
        'App\Values',
        'Database\Factories',
        'Database\Seeders',
    ])
    ->ignoring([
        'App\Repositories',
        'App\Services',
    ]);
