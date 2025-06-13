<?php

declare(strict_types=1);

use Beacon\Metrics\Metrics;
use Laravel\Cashier\Checkout;

arch()
    ->expect('App\Repositories')
    ->classes()
    ->toHaveSuffix('Repository')
    ->toOnlyBeUsedIn(['App\Services', 'App\Console\Commands', 'App\Repositories'])
    ->toOnlyUse([
        'App\Values',
        'App\Models',
        'App\Services',
        'App\Enums',
        'App\Repositories',
        Checkout::class,
        Metrics::class,
    ])
    ->ignoringGlobalFunctions()
    ->ignoring(vendorPackages());
