<?php

declare(strict_types=1);

use Tests\Unit\Actions\AppContext\SetAppContext;

it('can navigate to access tokens', function () {
    [$team, $user, $organization] = createOwner();

    SetAppContext::run($organization, $team);

    $this->actingAs($user);

    $page = visit(route('access-tokens.index'))
        ->resize(3024 / 2, 700);

    $page
        ->assertSee('No results.')
        ->screenshotElement('[data-test="main"]', 'access-tokens-initial');
});

it('can create an access token', function () {
    [$team, $user, $organization] = createOwner();

    SetAppContext::run($organization, $team);

    $this->actingAs($user);

    $page = visit(route('access-tokens.index'))
        ->resize(3024 / 2, 700);

    $page
        ->assertSee('No results.')
        ->screenshotElement('[data-test="main"]', 'access-tokens-initial')
        ->click('[data-test="button-access-token-create"]')
        ->assertSee('New Access Token')
        ->screenshotElement('[data-test="dialog-access-token-create"]', 'access-tokens-form-create')
        ->type('#name', 'Demo Access Token')
        ->click('[data-test="button-access-token-create-submit"]')
        ->assertSee('Demo Access Token')
        ->assertSee('Keep this token secure. It won\'t be shown again.')
        ->assertSee('sk_beacon_')
        ->screenshotElement('[data-test="main"]', 'access-tokens-after-create');
});

it('can delete access tokens', function () {
    [$team, $user, $organization] = createOwner();

    SetAppContext::run($organization, $team);

    $this->actingAs($user);

    $page = visit(route('access-tokens.index'))
        ->resize(3024 / 2, 700);

    $page
        ->assertSee('No results.')
        ->click('[data-test="button-access-token-create"]')
        ->assertSee('New Access Token')
        ->type('#name', 'Demo Access Token')
        ->click('[data-test="button-access-token-create-submit"]')
        ->assertSee('Demo Access Token')
        ->screenshotElement('[data-test="main"]', 'access-tokens-before-delete')
        ->click('[data-test="button-access-token-delete"]')
        ->assertSee('Delete Token')
        ->assertSee('Are you sure you want to delete the token "Demo Access Token"? This action cannot be undone.')
        ->screenshotElement('[data-test="dialog-access-token-delete"]', 'access-tokens-delete')
        ->click('[data-test="button-access-token-delete-confirm"]')
        ->assertSee('No results.')
        ->screenshotElement('[data-test="main"]', 'access-tokens-after-delete');
});
