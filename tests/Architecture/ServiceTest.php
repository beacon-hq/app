<?php

declare(strict_types=1);

arch()
    ->expect('App\Services')
    ->toOnlyUse([
        'App\Services',
        'App\Values',
        'App\Repositories',
    ])
    ->ignoringGlobalFunctions()
    ->ignoring(vendorPackages());
