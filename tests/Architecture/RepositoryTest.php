<?php

declare(strict_types=1);

arch()
    ->expect('App\Repositories')
    ->classes()
    ->toHaveSuffix('Repository')
    ->toOnlyBeUsedIn('App\Services')
    ->toOnlyUse([
        'App\Values',
        'App\Models',
        'App\Services',
    ])
    ->ignoringGlobalFunctions()
    ->ignoring(vendorPackages());
