<?php

declare(strict_types=1);

// beforeEach()->skip();

use App\Http\Controllers\ProfileController;
use Bag\Attributes\WithoutValidation;

arch()->expect('App\Http\Controllers')
    ->toOnlyUse([
        'App\Services',
        'App\Values',
        WithoutValidation::class,
    ])
    ->ignoringGlobalFunctions()
    ->ignoring([
        'App\Http\Controllers\Auth',
        ProfileController::class,
        'Illuminate',
        'Inertia',
    ]);
