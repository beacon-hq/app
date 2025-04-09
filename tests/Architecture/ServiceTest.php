<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\Team;
use App\Services\AppContextService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

arch()
    ->expect('App\Services')
    ->toOnlyUse([
        'App\Services',
        'App\Values',
        'App\Enums',
        'App\Repositories',
        'App\Notifications',
        App::class,
        Session::class,
    ])
    ->ignoring(AppContextService::class)
    ->ignoringGlobalFunctions()
    ->ignoring(vendorPackages());

arch()
    ->expect('App\Services\AppContextService')
    ->toOnlyUse([
        'App\Services',
        'App\Values',
        'App\Enums',
        'App\Repositories',
        'App\Notifications',
        App::class,
        Session::class,
        Organization::class,
        Team::class,
    ])
    ->ignoringGlobalFunctions()
    ->ignoring(vendorPackages());
