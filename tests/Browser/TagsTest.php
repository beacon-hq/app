<?php

declare(strict_types=1);

use App\Actions\AppContext\SetAppContext;
use App\Actions\Users\AssignRoleToUser;
use App\Enums\Role;
use App\Values\User as UserValue;

it('can create new tag', function () {
    [$team, $user, $organization] = createOwner();

    $this->actingAs($user);

    SetAppContext::run($organization, $team);

    AssignRoleToUser::run(UserValue::from($user), Role::OWNER);

    $page = visit(route('tags.index'))
        ->resize(3024 / 2, 600);

    $page
        ->assertSee('Tags')
        ->screenshotElement('[data-test="main"]', 'tags-initial')
        ->resize(3024 / 2, 800)
        ->click('[data-test="button-new-tag"]')
        ->assertSee('New Tag')
        ->screenshotElement('[data-test="main"]', 'tags-form-create')
        ->type('[data-test="input-tags-name"]', 'Demo Tag')
        ->type('[data-test="input-tags-description"]', 'This is a demo tag.')
        ->click('div.bg-lime-400')
        ->screenshotElement('[data-test="main"]', 'tags-form-create-fill')
        ->click('[data-test="button-tag-submit"]')
        ->assertSee('Demo Tag')
        ->screenshotElement('[data-test="card-tags"]', 'tags-after-create');
});

it('can view tags list', function () {
    [$team, $user, $organization] = createOwner();

    $this->actingAs($user);

    SetAppContext::run($organization, $team);

    AssignRoleToUser::run(UserValue::from($user), Role::OWNER);

    $page = visit(route('tags.index'))
        ->resize(3024 / 2, 700);

    $page
        ->assertSee('Tags')
        ->screenshotElement('[data-test="main"]', 'tags-list');
});

it('can edit tags', function () {
    [$team, $user, $organization] = createOwner();

    $this->actingAs($user);

    SetAppContext::run($organization, $team);

    AssignRoleToUser::run(UserValue::from($user), Role::OWNER);

    $page = visit(route('tags.index'))
        ->resize(3024 / 2, 800);

    $page
        ->assertSee('Tags')
        ->click('[data-test="button-new-tag"]')
        ->assertSee('New Tag')
        ->type('[data-test="input-tags-name"]', 'Edit Me Tag')
        ->type('[data-test="input-tags-description"]', 'This will be edited.')
        ->click('div.bg-lime-400')
        ->click('[data-test="button-tag-submit"]')
        ->assertSee('Edit Me Tag')
        ->click('tbody tr:first-child a[data-test="button-tags-edit"]')
        ->assertSee('Edit Tag')
        ->screenshotElement('[data-test="card-tags-edit"]', 'tags-edit')
        ->type('[data-test="input-tags-description"]', 'This is an updated demo tag.')
        ->screenshotElement('[data-test="main"]', 'tags-form-edit-fill')
        ->click('[data-test="button-tag-submit"]')
        ->assertSee('Tags')
        ->screenshotElement('[data-test="main"]', 'tags-after-edit');
});
