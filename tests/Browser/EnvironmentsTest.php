<?php

declare(strict_types=1);

use App\Actions\AppContext\SetAppContext;

it('can create a new environment', function () {
    [$team, $user, $organization] = createOwner();

    SetAppContext::run($organization, $team);

    $this->actingAs($user);

    visit(route('environments.index'))
        ->resize(3024 / 2, 700)
        ->assertSee('No Environments')
        ->screenshotElement('[data-test="main"]', 'environments-initial')
        ->click('[data-test="button-create-environment"]')
        ->screenshotElement('[data-test="main"]', 'environments-form-create')
        ->type('#name', 'Demo')
        ->type('#description', 'This is a demo environment')
        ->click('div.bg-sky-400')
        ->click('[data-test="submit-environment"]')
        ->assertSee('Demo')
        ->assertSee('This is a demo environment')
        ->screenshotElement('[data-test="main"]', 'environments-after-create')
        ->screenshotElement('[data-test="card-environment"]', 'environments-environment-card');
});

it('can edit environments', function () {
    [$team, $user, $organization] = createOwner();

    SetAppContext::run($organization, $team);

    $this->actingAs($user);

    visit(route('environments.index'))
        ->resize(3024 / 2, 700)
        ->assertSee('No Environments')
        ->click('[data-test="button-create-environment"]')
        ->type('#name', 'Demo')
        ->type('#description', 'This is a demo environment')
        ->click('div.bg-sky-400')
        ->click('[data-test="submit-environment"]')
        ->assertSee('Demo')
        ->click('[data-test="card-environment"] a')
        ->resize(3024 / 2, 900)
        ->assertSee('Edit Environment')
        ->screenshotElement('[data-test="card-environment-edit"]', 'environments-edit')
        ->assertDisabled('#name')
        ->type('#description', 'This is an updated demo environment')
        ->click('div.bg-purple-400')
        ->click('[data-test="submit-environment"]')
        ->assertSee('This is an updated demo environment')
        ->screenshotElement('[data-test="main"]', 'environments-after-edit');
});
