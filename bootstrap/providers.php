<?php

declare(strict_types=1);

use App\Providers\ApiRateLimitServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\FeatureFlagServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\HorizonServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    HorizonServiceProvider::class,
    FeatureFlagServiceProvider::class,
    ApiRateLimitServiceProvider::class,
];
