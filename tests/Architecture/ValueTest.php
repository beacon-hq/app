<?php

declare(strict_types=1);

use App\Values\AppContext;
use App\Values\FeatureFlagContext;
use App\Values\FeatureFlagResponse;
use App\Values\PolicyDefinition;
use App\Values\TypescriptTransformer;
use Bag\Attributes\MapName;

arch()
    ->expect('App\\Values')
    ->classes()
    ->toHaveAttribute(MapName::class)
    ->ignoring([
        'App\Values\Casts',
        'App\Values\Factories',
        'App\Values\Collections',
        TypescriptTransformer::class
    ])
    ->toHaveMethod('fromModel')
    ->ignoring([
        'App\Values\Casts',
        'App\Values\Factories',
        'App\Values\Collections',
        AppContext::class,
        FeatureFlagContext::class,
        PolicyDefinition::class,
        FeatureFlagResponse::class,
        TypescriptTransformer::class,
    ]);
