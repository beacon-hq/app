<?php

declare(strict_types=1);

// beforeEach()->skip();

use App\Http\Controllers\ProfileController;
use Bag\Attributes\WithoutValidation;
use Illuminate\Support\Facades\Session;
use Laravel\Cashier\Checkout;

arch()->expect('App\Http\Controllers')
    ->toOnlyUse([
        'App\Services',
        'App\Values',
        'App\Enums',
        WithoutValidation::class,
        Session::class,
        Checkout::class,
    ])
    ->ignoringGlobalFunctions()
    ->ignoring([
        'App\Http\Controllers\Auth',
        ProfileController::class,
        'Illuminate',
        'Inertia',
    ]);
