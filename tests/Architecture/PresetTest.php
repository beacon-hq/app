<?php

declare(strict_types=1);

use App\Http\Controllers\Api\OnboardingController;
use App\Http\Controllers\FeatureTypeController;
use App\Http\Middleware\RequestTimingMiddleware;

arch('it does not call dd()')->expect('dd')->not->toBeUsed();
arch('it does not call ddd()')->expect('ddd')->not->toBeUsed();
arch('it does not call dump()')->expect('dump')->not->toBeUsed();
arch('it does not call debug()')->expect('debug')->not->toBeUsed();
arch('it does not call xdebug_break()')->expect('xdebug_break')->not->toBeUsed();

arch()->preset()->php();

arch()
    ->preset()
    ->laravel()
    ->ignoring([
        'App\Enums\Traits',
        FeatureTypeController::class,
        OnboardingController::class,
        RequestTimingMiddleware::class,
    ]);

arch()->expect('App')->toUseStrictTypes()->toUseStrictEquality();
