<?php

declare(strict_types=1);

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\Fixtures\Models\LazilyResolved;

beforeEach(function () {
    Schema::table('users', function (Blueprint $table) {
        $table->softDeletes();
    });
});

it('explicitly resolves the model', function () {
    $user = User::factory()->create();

    $model = new LazilyResolved();
    $lazy = $model->resolveRouteBinding($user->id);

    expect($lazy)->toBeInstanceOf(LazilyResolved::class)
        ->and(property($lazy, 'deferredInit'))->toBeAnonymousClass()
        ->and(property($lazy, 'deferredInitResolved'))->toBeFalse()
        ->and(property($lazy, 'attributes'))->toBeEmpty();

    $lazy();

    expect(property($lazy, 'deferredInit'))->toBeNull()
        ->and(property($lazy, 'deferredInitResolved'))->toBeTrue()
        ->and(property($lazy, 'attributes'))->toBe([
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'email_verified_at' => $user->getRawOriginal('email_verified_at'),
            'password' => $user->getRawOriginal('password'),
            'remember_token' => $user->getRawOriginal('remember_token'),
            'created_at' => $user->getRawOriginal('created_at'),
            'updated_at' => $user->getRawOriginal('updated_at'),
            'deleted_at' => null,
        ]);
});

it('implicitly resolves the model on property access', function () {
    $user = User::factory()->create();

    $model = new LazilyResolved();
    $lazy = $model->resolveRouteBinding($user->id);

    expect($lazy)->toBeInstanceOf(LazilyResolved::class)
        ->and(property($lazy, 'deferredInit'))->toBeAnonymousClass()
        ->and(property($lazy, 'deferredInitResolved'))->toBeFalse()
        ->and(property($lazy, 'attributes'))->toBeEmpty();

    $lazy->first_name;

    expect(property($lazy, 'deferredInit'))->toBeNull()
        ->and(property($lazy, 'deferredInitResolved'))->toBeTrue()
        ->and(property($lazy, 'attributes'))->toBe([
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'email_verified_at' => $user->getRawOriginal('email_verified_at'),
            'password' => $user->getRawOriginal('password'),
            'remember_token' => $user->getRawOriginal('remember_token'),
            'created_at' => $user->getRawOriginal('created_at'),
            'updated_at' => $user->getRawOriginal('updated_at'),
            'deleted_at' => null,
        ]);
});

it('implicitly resolves the model on property write', function () {
    $user = User::factory()->create();

    $model = new LazilyResolved();
    $lazy = $model->resolveRouteBinding($user->id);

    expect($lazy)->toBeInstanceOf(LazilyResolved::class)
        ->and(property($lazy, 'deferredInit'))->toBeAnonymousClass()
        ->and(property($lazy, 'deferredInitResolved'))->toBeFalse()
        ->and(property($lazy, 'attributes'))->toBeEmpty();

    $lazy->first_name = 'Test 2';

    expect(property($lazy, 'deferredInit'))->toBeNull()
        ->and(property($lazy, 'deferredInitResolved'))->toBeTrue()
        ->and(property($lazy, 'attributes'))->toBe([
            'id' => $user->id,
            'first_name' => 'Test 2',
            'last_name' => $user->last_name,
            'email' => $user->email,
            'email_verified_at' => $user->getRawOriginal('email_verified_at'),
            'password' => $user->getRawOriginal('password'),
            'remember_token' => $user->getRawOriginal('remember_token'),
            'created_at' => $user->getRawOriginal('created_at'),
            'updated_at' => $user->getRawOriginal('updated_at'),
            'deleted_at' => null,
        ]);
});

it('implicitly resolves the model on toArray', function () {
    $user = User::factory()->create();

    $model = new LazilyResolved();
    $lazy = $model->resolveRouteBinding($user->id);

    expect($lazy)->toBeInstanceOf(LazilyResolved::class)
        ->and(property($lazy, 'deferredInit'))->toBeAnonymousClass()
        ->and(property($lazy, 'deferredInitResolved'))->toBeFalse()
        ->and(property($lazy, 'attributes'))->toBeEmpty();

    $array = $lazy->toArray();

    expect(property($lazy, 'deferredInit'))->toBeNull()
        ->and(property($lazy, 'deferredInitResolved'))->toBeTrue()
        ->and($array)->toBe([
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'email_verified_at' => $user->getRawOriginal('email_verified_at'),
            'password' => $user->getRawOriginal('password'),
            'remember_token' => $user->getRawOriginal('remember_token'),
            // Format to match 2025-01-01T07:12:36.000000Z
            'created_at' => $user->created_at->format('Y-m-d\TH:i:s.u\Z'),
            'updated_at' => $user->updated_at->format('Y-m-d\TH:i:s.u\Z'),
            'deleted_at' => null,
        ]);
});

it('implicitly resolves the model on toJson', function () {
    $user = User::factory()->create();

    $model = new LazilyResolved();
    $lazy = $model->resolveRouteBinding($user->id);

    expect($lazy)->toBeInstanceOf(LazilyResolved::class)
        ->and(property($lazy, 'deferredInit'))->toBeAnonymousClass()
        ->and(property($lazy, 'deferredInitResolved'))->toBeFalse()
        ->and(property($lazy, 'attributes'))->toBeEmpty();

    $json = $lazy->toJson();

    expect(property($lazy, 'deferredInit'))->toBeNull()
        ->and(property($lazy, 'deferredInitResolved'))->toBeTrue()
        ->and($json)->toBe(json_encode([
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'email_verified_at' => $user->getRawOriginal('email_verified_at'),
            'password' => $user->getRawOriginal('password'),
            'remember_token' => $user->getRawOriginal('remember_token'),
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'deleted_at' => null,
        ]));
});

it('implicitly resolves the model on json_encode', function () {
    $user = User::factory()->create();

    $model = new LazilyResolved();
    $lazy = $model->resolveRouteBinding($user->id);

    expect($lazy)->toBeInstanceOf(LazilyResolved::class)
        ->and(property($lazy, 'deferredInit'))->toBeAnonymousClass()
        ->and(property($lazy, 'deferredInitResolved'))->toBeFalse()
        ->and(property($lazy, 'attributes'))->toBeEmpty();

    $json = json_encode($lazy);

    expect(property($lazy, 'deferredInit'))->toBeNull()
        ->and(property($lazy, 'deferredInitResolved'))->toBeTrue()
        ->and($json)->toBe(json_encode([
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'email_verified_at' => $user->getRawOriginal('email_verified_at'),
            'password' => $user->getRawOriginal('password'),
            'remember_token' => $user->getRawOriginal('remember_token'),
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'deleted_at' => null,
        ]));
});

it('implicitly resolves the model on update', function (string $method) {
    $user = User::factory()->create();

    $model = new LazilyResolved();
    $lazy = $model->resolveRouteBinding($user->id);

    expect($lazy)->toBeInstanceOf(LazilyResolved::class)
        ->and(property($lazy, 'deferredInit'))->toBeAnonymousClass()
        ->and(property($lazy, 'deferredInitResolved'))->toBeFalse()
        ->and(property($lazy, 'attributes'))->toBeEmpty();

    Carbon::setTestNow(now()->addSecond());

    $lazy->{$method}([
        'first_name' => 'Test 2',
        'last_name' => 'User 2',
        'email' => 'davey@php.net',
    ]);

    expect(property($lazy, 'deferredInit'))->toBeNull()
        ->and(property($lazy, 'deferredInitResolved'))->toBeTrue()
        ->and(property($lazy, 'attributes'))->toBe([
            'id' => $user->id,
            'first_name' => 'Test 2',
            'last_name' => 'User 2',
            'email' => 'davey@php.net',
            'email_verified_at' => $user->getRawOriginal('email_verified_at'),
            'password' => $user->getRawOriginal('password'),
            'remember_token' => $user->getRawOriginal('remember_token'),
            'created_at' => $user->getRawOriginal('created_at'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
            'deleted_at' => null,
        ]);
})->with(['update', 'updateQuietly', 'updateOrFail']);

it('implicitly resolves the model on delete', function (string $method) {
    $user = User::factory()->create();

    $model = new LazilyResolved();
    $lazy = $model->resolveRouteBinding($user->id);

    expect($lazy)->toBeInstanceOf(LazilyResolved::class)
        ->and(property($lazy, 'deferredInit'))->toBeAnonymousClass()
        ->and(property($lazy, 'deferredInitResolved'))->toBeFalse()
        ->and(property($lazy, 'attributes'))->toBeEmpty();

    $lazy->{$method}();

    expect(property($lazy, 'deferredInit'))->toBeNull()
        ->and(property($lazy, 'deferredInitResolved'))->toBeTrue()
        ->and(property($lazy, 'attributes'))->toBe([
            'id' => $user->id,
            'first_name' => $user->getRawOriginal('first_name'),
            'last_name' => $user->getRawOriginal('last_name'),
            'email' => $user->getRawOriginal('email'),
            'email_verified_at' => $user->getRawOriginal('email_verified_at'),
            'password' => $user->getRawOriginal('password'),
            'remember_token' => $user->getRawOriginal('remember_token'),
            'created_at' => $user->getRawOriginal('created_at'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
            'deleted_at' => now()->format('Y-m-d H:i:s'),
        ])
        ->and(LazilyResolved::count())->toBe(0);
})->with(['delete', 'deleteQuietly', 'deleteOrFail']);



it('implicitly resolves the model on __toString', function () {
    $user = User::factory()->create();

    $model = new LazilyResolved();
    $lazy = $model->resolveRouteBinding($user->id);

    expect($lazy)->toBeInstanceOf(LazilyResolved::class)
        ->and(property($lazy, 'deferredInit'))->toBeAnonymousClass()
        ->and(property($lazy, 'deferredInitResolved'))->toBeFalse()
        ->and(property($lazy, 'attributes'))->toBeEmpty();

    (string) $lazy;

    expect(property($lazy, 'deferredInit'))->toBeNull()
        ->and(property($lazy, 'deferredInitResolved'))->toBeTrue()
        ->and(property($lazy, 'attributes'))->toBe([
            'id' => $user->id,
            'first_name' => $user->getRawOriginal('first_name'),
            'last_name' => $user->getRawOriginal('last_name'),
            'email' => $user->getRawOriginal('email'),
            'email_verified_at' => $user->getRawOriginal('email_verified_at'),
            'password' => $user->getRawOriginal('password'),
            'remember_token' => $user->getRawOriginal('remember_token'),
            'created_at' => $user->getRawOriginal('created_at'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
            'deleted_at' => null,
        ]);
});
