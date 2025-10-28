<?php

declare(strict_types=1);

use App\Actions\AppContext\SetAppContext;

it('can create new application', function () {
    [$team, $user, $organization] = createOwner();

    SetAppContext::run($organization, $team);

    $this->actingAs($user);

    $page = visit(route('applications.index'))
        ->resize(3024 / 2, 700);

    $page
        ->assertSee('No Applications')
        ->screenshotElement('[data-test="main"]', 'applications-initial')
        ->click('[data-test="button-new-application"]')
        ->assertSee('New Application')
        ->type('#name', 'Demo Application')
        ->type('#description', 'This is a demo application.')
        ->type('#display_name', 'Demo App')
        ->click('div.bg-lime-400')
        ->click('[data-test="button-application-submit"]')
        ->assertSee('Demo App')
        ->screenshotElement('[data-test="main"]', 'applications-after-create')
        ->screenshotElement('[data-test="card-application"]', 'applications-app-card');
});

it('can edit applications', function () {
    [$team, $user, $organization] = createOwner();

    SetAppContext::run($organization, $team);

    $this->actingAs($user);

    $page = visit(route('applications.index'))
        ->resize(3024 / 2, 700);

    $page
        ->assertSee('No Applications')
        ->click('[data-test="button-new-application"]')
        ->assertSee('New Application')
        ->type('#name', 'Demo Application')
        ->type('#description', 'This is a demo application.')
        ->type('#display_name', 'Demo App')
        ->click('div.bg-lime-400')
        ->click('[data-test="button-application-submit"]')
        ->assertSee('Demo App')
        ->click('[data-test="card-application"] a')
        ->resize(3024 / 2, 900)
        ->assertSee('Edit Application')
        ->screenshotElement('[data-test="card-application-edit"]', 'applications-edit')
        ->assertDisabled('#name')
        ->type('#description', 'This is an updated demo application.')
        ->type('#display_name', 'Updated Demo App')
        ->click('div.bg-purple-400')
        ->click('[data-test="button-application-submit"]')
        ->assertSee('Updated Demo App')
        ->screenshotElement('[data-test="main"]', 'applications-after-edit')
        ->screenshotElement('[data-test="card-application"]', 'applications-app-card-updated');
});
