<?php

declare(strict_types=1);

// beforeEach()->skip();

use App\Http\Controllers\ProfileController;
use Bag\Attributes\WithoutValidation;
use Bag\Values\Optional;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use Laravel\Cashier\Checkout;
use Symfony\Component\HttpFoundation\Response;

arch()->expect('App\Http\Controllers')
    ->toOnlyUse([
        'App\Services',
        'App\Values',
        'App\Enums',
        'App\Events',
        WithoutValidation::class,
        Session::class,
        Checkout::class,
        Response::class,
        Gate::class,
        Optional::class,
    ])
    ->ignoringGlobalFunctions()
    ->ignoring([
        'App\Http\Controllers\Auth',
        ProfileController::class,
        'Illuminate',
        'Inertia',
    ]);
