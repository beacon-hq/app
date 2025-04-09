<?php

declare(strict_types=1);

arch()
    ->expect('App\Repositories')
    ->classes()
    ->toHaveSuffix('Repository')
    ->toOnlyBeUsedIn(['App\Services', 'App\Console\Commands'])
    ->toOnlyUse([
        'App\Values',
        'App\Models',
        'App\Services',
        'App\Enums',
    ])
    ->ignoringGlobalFunctions()
    ->ignoring(vendorPackages());
