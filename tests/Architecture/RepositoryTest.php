<?php

declare(strict_types=1);

use Beacon\Metrics\Metrics;
use Beacon\Metrics\Values\Collections\TrendMetricCollection;
use Beacon\Metrics\Values\TrendMetric;
use Laravel\Cashier\Checkout;
use Laravel\Cashier\Invoice;
use Laravel\Cashier\Subscription;
use Laravel\Sanctum\NewAccessToken;

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
        Invoice::class,
        Subscription::class,
        NewAccessToken::class,
        TrendMetricCollection::class,
        TrendMetric::class,
    ])
    ->ignoringGlobalFunctions()
    ->ignoring(vendorPackages());
