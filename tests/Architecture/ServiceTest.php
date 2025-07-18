<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\Team;
use App\Services\AppContextService;
use Brick\Money\Money;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Checkout;
use Symfony\Component\HttpFoundation\Response;

arch()
    ->expect('App\Services')
    ->toOnlyUse([
        'App\Services',
        'App\Values',
        'App\Enums',
        'App\Repositories',
        'App\Notifications',
        'App\Events',
        App::class,
        Session::class,
        Checkout::class,
        Cashier::class,
        Money::class,
        Response::class,
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
