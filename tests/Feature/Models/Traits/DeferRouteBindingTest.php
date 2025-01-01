<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use function Pest\Laravel\getJson;
use Tests\Fixtures\Models\LazilyResolved;
use Tests\Fixtures\Models\ParentModel;
use Tests\Fixtures\Models\RegularModel;

beforeEach(function () {
    Schema::table('users', function (Blueprint $table) {
        $table->softDeletes();
    });
});

it('can fetch models without being lazy', function () {
    $user = User::factory()->create();

    $lazilyResolved = LazilyResolved::find($user->id);

    expect($lazilyResolved)->toBeInstanceOf(LazilyResolved::class)
        ->and(property($lazilyResolved, 'deferredInitResolved'))->toBeNull()
        ->and($lazilyResolved->getRawOriginal())->toEqual([...$user->getRawOriginal(), 'deleted_at' => null]);
});

it('lazily injects the model', function () {
    $injected = null;

    Route::get('/{lazy}', function (LazilyResolved $lazy) use (&$injected) {
        $injected = $lazy;
    })->middleware(SubstituteBindings::class);

    $user = User::factory()->create();

    getJson('/'.$user->id);

    expect($injected)->toBeInstanceOf(LazilyResolved::class)
        ->and(property($injected, 'deferredInitResolved'))->toBeFalse()
        ->and(property($injected, 'deferredInit'))->toBeAnonymousClass()
        ->and($injected()->getRawOriginal())->toEqual([...$user->getRawOriginal(), 'deleted_at' => null]);
});

it('lazily injects soft deleted models', function () {
    $injected = null;

    Route::get('/{lazy}', function (LazilyResolved $lazy) use (&$injected) {
        $injected = $lazy;
    })->middleware(SubstituteBindings::class)->withTrashed();

    $user = User::factory()->create();

    LazilyResolved::find($user->id)->delete();

    getJson('/'.$user->id);

    expect($injected)->toBeInstanceOf(LazilyResolved::class)
        ->and(property($injected, 'deferredInitResolved'))->toBeFalse()
        ->and(property($injected, 'deferredInit'))->toBeAnonymousClass()
        ->and(property($injected, 'deferredInit')->method)->toBe('resolveRouteBindingQuery')
        ->and(property($injected, 'deferredInit')->value)->toBe('1')
        ->and(property($injected, 'deferredInit')->field)->toBe(null)
        ->and($injected()->getRawOriginal())->toEqual([...$user->getRawOriginal(), 'deleted_at' => now()]);
});

it('lazily injects with custom slug', function () {
    $injected = null;

    Route::get('/{lazy:first_name}', function (LazilyResolved $lazy) use (&$injected) {
        $injected = $lazy;
    })->middleware(SubstituteBindings::class);

    $user = User::factory()->create();
    getJson('/'.$user->first_name);

    expect($injected)->toBeInstanceOf(LazilyResolved::class)
        ->and(property($injected, 'deferredInitResolved'))->toBeFalse()
        ->and(property($injected, 'deferredInit'))->toBeAnonymousClass()
        ->and(property($injected, 'deferredInit')->method)->toBe('resolveRouteBindingQuery')
        ->and(property($injected, 'deferredInit')->value)->toBe($user->first_name)
        ->and(property($injected, 'deferredInit')->field)->toBe('first_name')
        ->and($injected()->getRawOriginal())->toEqual([...$user->getRawOriginal(), 'deleted_at' => null]);
});

it('lazily injects with scoped bindings', function () {
    $injectedChild = null;
    $injectedParent = null;

    Route::get('/parent/{parent}/lazy/{lazy}', function (ParentModel $parent, LazilyResolved $lazy) use (&$injectedParent, &$injectedChild) {
        $injectedParent = $parent;
        $injectedChild = $lazy;
    })->middleware(SubstituteBindings::class)->scopeBindings();

    $parent = ParentModel::factory()->create();
    $user = User::factory()->hasAttached($parent)->create();
    getJson('/parent/'.$parent->id.'/lazy/'.$user->id);

    expect($injectedChild)->toBeInstanceOf(LazilyResolved::class)
        ->and(property($injectedChild, 'deferredInitResolved'))->toBeFalse()
        ->and(property($injectedChild, 'deferredInit'))->toBeAnonymousClass()
        ->and($injectedChild()->getRawOriginal())->toEqual([
            ...$user->getRawOriginal(),
            'deleted_at' => null,
        ])
        ->and($injectedParent)->toBeInstanceOf(ParentModel::class)
        ->and(property($injectedParent, 'deferredInitResolved'))->toBeTrue()
        ->and(property($injectedParent, 'deferredInit'))->toBeNull()
        ->and($injectedParent()->getRawOriginal())->toEqual($parent->getRawOriginal());
});

it('lazily injects with scoped bindings only on parent', function () {
    $injectedChild = null;
    $injectedParent = null;

    Route::get('/parent/{parent}/child/{user}', function (ParentModel $parent, RegularModel $user) use (&$injectedParent, &$injectedChild) {
        $injectedParent = $parent;
        $injectedChild = $user;
    })->middleware(SubstituteBindings::class)->scopeBindings();

    $parent = ParentModel::factory()->create();
    $user = User::factory()->hasAttached($parent)->create();
    getJson('/parent/'.$parent->id.'/child/'.$user->id);

    expect($injectedChild)->toBeInstanceOf(RegularModel::class)
        ->and(property($injectedChild, 'deferredInitResolved'))->toBeNull()
        ->and(property($injectedChild, 'deferredInit'))->toBeNull()
        ->and($injectedChild->getRawOriginal())->toEqual([
            ...$user->getRawOriginal(),
            'deleted_at' => null,
            'pivot_tenant_id' => $parent->id,
            'pivot_user_id' => $user->id,
            'pivot_created_at' => now(),
            'pivot_updated_at' => now(),
        ])
        ->and($injectedParent)->toBeInstanceOf(ParentModel::class)
        ->and(property($injectedParent, 'deferredInitResolved'))->toBeTrue()
        ->and(property($injectedParent, 'deferredInit'))->toBeNull()
        ->and($injectedParent()->getRawOriginal())->toEqual($parent->getRawOriginal());
});

it('lazily injects with scoped bindings and trashed', function () {
    $injectedChild = null;
    $injectedParent = null;

    Route::get('/parent/{parent}/child/{lazy}', function (ParentModel $parent, LazilyResolved $lazy) use (&$injectedParent, &$injectedChild) {
        $injectedParent = $parent;
        $injectedChild = $lazy;
    })->middleware(SubstituteBindings::class)->scopeBindings()->withTrashed();

    $parent = ParentModel::factory()->create();
    $user = User::factory()->hasAttached($parent)->create();
    LazilyResolved::find($user->id)->delete();

    getJson('/parent/'.$parent->id.'/child/'.$user->id);

    expect($injectedChild)->toBeInstanceOf(LazilyResolved::class)
        ->and(property($injectedChild, 'deferredInitResolved'))->toBeFalse()
        ->and(property($injectedChild, 'deferredInit'))->toBeAnonymousClass()
        ->and($injectedChild()->getRawOriginal())->toEqual([
            ...$user->getRawOriginal(),
            'deleted_at' => now(),
        ])
        ->and($injectedParent)->toBeInstanceOf(ParentModel::class)
        ->and(property($injectedParent, 'deferredInitResolved'))->toBeTrue()
        ->and(property($injectedParent, 'deferredInit'))->toBeNull()
        ->and($injectedParent()->getRawOriginal())->toEqual($parent->getRawOriginal());
});

it('lazily injects with scoped bindings only on parent and trashed', function () {
    $injectedChild = null;
    $injectedParent = null;

    Route::get('/parent/{parent}/child/{user}', function (ParentModel $parent, RegularModel $user) use (&$injectedParent, &$injectedChild) {
        $injectedParent = $parent;
        $injectedChild = $user;
    })->middleware(SubstituteBindings::class)->scopeBindings()->withTrashed();

    $parent = ParentModel::factory()->create();
    $user = User::factory()->hasAttached($parent)->create();
    LazilyResolved::find($user->id)->delete();
    getJson('/parent/'.$parent->id.'/child/'.$user->id);

    expect($injectedChild)->toBeInstanceOf(RegularModel::class)
        ->and(property($injectedChild, 'deferredInitResolved'))->toBeNull()
        ->and(property($injectedChild, 'deferredInit'))->toBeNull()
        ->and($injectedChild->getRawOriginal())->toEqual([
            ...$user->getRawOriginal(),
            'deleted_at' => now(),
            'pivot_tenant_id' => $parent->id,
            'pivot_user_id' => $user->id,
            'pivot_created_at' => now(),
            'pivot_updated_at' => now(),
        ])
        ->and($injectedParent)->toBeInstanceOf(ParentModel::class)
        ->and(property($injectedParent, 'deferredInitResolved'))->toBeTrue()
        ->and(property($injectedParent, 'deferredInit'))->toBeNull()
        ->and($injectedParent()->getRawOriginal())->toEqual($parent->getRawOriginal());
});

it('lazily injects multiple', function () {
    $injectedChild = null;
    $injectedParent = null;

    Route::get('/parent/{parent}/child/{lazy}', function (ParentModel $parent, LazilyResolved $lazy) use (&$injectedParent, &$injectedChild) {
        $injectedParent = $parent;
        $injectedChild = $lazy;
    })->middleware(SubstituteBindings::class)->withoutScopedBindings();

    $tenant = ParentModel::factory()->create();
    $user = User::factory()->hasAttached($tenant)->create();
    getJson('/parent/'.$tenant->id.'/child/'.$user->id);

    expect($injectedChild)->toBeInstanceOf(LazilyResolved::class)
        ->and(property($injectedChild, 'deferredInitResolved'))->toBeFalse()
        ->and(property($injectedChild, 'deferredInit'))->toBeAnonymousClass()
        ->and($injectedChild()->getRawOriginal())->toEqual([
            ...$user->getRawOriginal(),
            'deleted_at' => null,
        ])
        ->and($injectedParent)->toBeInstanceOf(ParentModel::class)
        ->and(property($injectedParent, 'deferredInitResolved'))->toBeFalse()
        ->and(property($injectedParent, 'deferredInit'))->toBeAnonymousClass()
        ->and($injectedParent()->getRawOriginal())->toEqual($tenant->getRawOriginal());
});
