<?php

declare(strict_types=1);

// beforeEach()->skip();

use App\Http\Controllers\ProfileController;
use Bag\Attributes\WithoutValidation;
use Illuminate\Support\Facades\Session;

arch()->expect('App\Http\Controllers')
    ->toOnlyUse([
        'App\Services',
        'App\Values',
        'App\Enums',
        WithoutValidation::class,
        Session::class,
    ])
    ->ignoringGlobalFunctions()
    ->ignoring([
        'App\Http\Controllers\Auth',
        ProfileController::class,
        'Illuminate',
        'Inertia',
    ]);
