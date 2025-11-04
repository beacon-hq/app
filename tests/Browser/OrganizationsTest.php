<?php

declare(strict_types=1);

use App\Actions\AppContext\SetAppContext;
use Laravel\Pennant\Feature;

it('can create new organization', function () {
    [$team, $user, $organization] = createOwner();

    $this->actingAs($user);

    SetAppContext::run($organization, $team);

    visit(route('organizations.index'))
        ->resize(3024 / 2, 600)
        ->assertSee('Organizations')
        ->screenshotElement('[data-test="main"]', 'organizations-initial')
        ->resize(3024 / 2, 700)
        ->click('[data-test="button-new-organization"]')
        ->assertSee('New Organization')
        ->screenshotElement('[data-test="main"]', 'organizations-form-create')
        ->wait(1)
        ->refresh()
        ->wait(1)
        ->click('[data-test="button-new-organization"]')
        ->wait(1)
        ->type('[data-test="input-organization-name"]', 'Demo Organization')
        ->type('[data-test="input-team-name"]', 'Demo Team')
        ->click('[data-test="icon-picker"]')
        ->assertSee('Search for an icon')
        ->type('[data-test="input-icon-picker-search"]', 'flag')
        ->wait(1)
        ->click('[data-test="icon-picker-icon-0"]')
        ->hover('.bg-sky-400')
        ->wait(1)
        ->click('.bg-sky-400')
        ->screenshot(filename: 'organization-form-create-fill')
        ->click('[data-test="button-organization-submit"]')
        ->wait(1)
        ->navigate(route('organizations.index'))
        ->assertSee('Demo Organization')
        ->screenshotElement(':nth-match([data-test="card-organization"], 1)', 'organizations-after-create')
        ->screenshotElement('[data-test="main"]', 'organizations-list');
});

it('can view organizations list', function () {
    [$team, $user, $organization] = createOwner();

    $this->actingAs($user);

    SetAppContext::run($organization, $team);

    visit(route('organizations.index'))
        ->resize(3024 / 2, 700)
        ->assertSee('Organizations');
});

it('can edit organizations', function () {
    [$team, $user, $organization] = createOwner();

    $this->actingAs($user);

    SetAppContext::run(null, $team);

    visit(route('organizations.index'))
        ->assertSee('Organizations')
        ->click('[data-test="button-new-organization"]')
        ->assertSee('New Organization')
        ->wait(1)
        ->refresh()
        ->wait(1)
        ->click('[data-test="button-new-organization"]')
        ->wait(1)
        ->type('[data-test="input-organization-name"]', 'Demo Organization')
        ->type('[data-test="input-team-name"]', 'Demo Team')
        ->click('[data-test="icon-picker"]')
        ->assertSee('Search for an icon')
        ->type('[data-test="input-icon-picker-search"]', 'flag')
        ->wait(1)
        ->click('[data-test="icon-picker-icon-0"]')
        ->hover('.bg-sky-400')
        ->wait(1)
        ->click('.bg-sky-400')
        ->click('[data-test="button-organization-submit"]')
        ->wait(1)
        ->navigate(route('organizations.index'))
        ->assertSee('Demo Organization');

    visit(route('organizations.index'))
        ->resize(3024 / 2, 800)
        ->assertSee('Organizations')
        ->click(':nth-match([data-test="card-organization"], 1)')
        ->resize(3024 / 2, 900)
        ->assertSee('Delete Organization')
        ->wait(1)
        ->screenshotElement('[data-test="card-organizations-edit"]', 'organizations-edit')
        ->type('[data-test="input-organization-name"]', 'Updated Demo Organization')
        ->click('[data-test="button-organization-submit"]')
        ->assertSee('Updated Demo Organization')
        ->screenshotElement('[data-test="main"]', 'organizations-after-edit');
});

it('can delete organization', function () {
    [$team, $user, $organization] = createOwner();

    $this->actingAs($user);

    SetAppContext::run($organization, $team);

    visit(route('organizations.index'))
        ->resize(3024 / 2, 600)
        ->assertSee('Organizations')
        ->resize(3024 / 2, 700)
        ->click('[data-test="button-new-organization"]')
        ->assertSee('New Organization')
        ->wait(1)
        ->refresh()
        ->wait(1)
        ->click('[data-test="button-new-organization"]')
        ->wait(1)
        ->type('[data-test="input-organization-name"]', 'Demo Organization')
        ->type('[data-test="input-team-name"]', 'Demo Team')
        ->click('[data-test="icon-picker"]')
        ->type('[data-test="input-icon-picker-search"]', 'flag')
        ->wait(1)
        ->click('[data-test="icon-picker-icon-0"]')
        ->hover('.bg-sky-400')
        ->wait(1)
        ->click('.bg-sky-400')
        ->click('[data-test="button-organization-submit"]')
        ->wait(1)
        ->navigate(route('organizations.index'))
        ->assertSee('Demo Organization')
        ->click(':nth-match([data-test="card-organization"], 1)')
        ->resize(3024 / 2, 900)
        ->assertSee('Delete Organization')
        ->wait(1)
        ->screenshotElement('[data-test="card-organizations-delete"]', 'organizations-delete')
        ->click('[data-test="button-organizations-delete"]')
        ->screenshotElement('[data-test="alert-organization-delete"]', 'organizations-confirm-delete');
});

it('is prompted to choose an organization and team on login', function () {
    [$team, $user, $organization] = createOwner();

    $this->actingAs($user);

    SetAppContext::run($organization, $team);

    Feature::define('billing-enabled', fn () => false);

    visit(route('organizations.index'))
        ->resize(3024 / 2, 700)
        ->assertSee('Organizations')
        ->click('[data-test="button-new-organization"]')
        ->assertSee('New Organization')
        ->wait(1)
        ->refresh()
        ->wait(1)
        ->click('[data-test="button-new-organization"]')
        ->wait(1)
        ->type('[data-test="input-organization-name"]', 'Demo Organization')
        ->type('[data-test="input-team-name"]', 'Demo Team')
        ->click('[data-test="icon-picker"]')
        ->type('[data-test="input-icon-picker-search"]', 'flag')
        ->wait(1)
        ->click('[data-test="icon-picker-icon-0"]')
        ->hover('.bg-sky-400')
        ->wait(1)
        ->click('.bg-sky-400')
        ->click('[data-test="button-organization-submit"]')
        ->wait(1)
        ->navigate(route('organizations.index'))
        ->assertSee('Demo Organization');

    visit(route('teams.select'))
        ->resize(600, 730)
        ->wait(1)
        ->screenshotElement('[data-test="main"]', 'login-select-team');
});

it('can switch organization and team', function () {
    [$team, $user, $organization] = createOwner();

    $this->actingAs($user);

    SetAppContext::run($organization, $team);

    Feature::define('billing-enabled', fn () => false);

    visit(route('organizations.index'))
        ->resize(3024 / 2, 600)
        ->assertSee('Organizations')
        ->resize(3024 / 2, 700)
        ->click('[data-test="button-new-organization"]')
        ->assertSee('New Organization')
        ->wait(1)
        ->refresh()
        ->wait(1)
        ->click('[data-test="button-new-organization"]')
        ->wait(1)
        ->type('[data-test="input-organization-name"]', 'Demo Organization')
        ->type('[data-test="input-team-name"]', 'Demo Team')
        ->click('[data-test="icon-picker"]')
        ->type('[data-test="input-icon-picker-search"]', 'flag')
        ->wait(1)
        ->click('[data-test="icon-picker-icon-0"]')
        ->hover('.bg-sky-400')
        ->wait(1)
        ->click('.bg-sky-400')
        ->click('[data-test="button-organization-submit"]')
        ->wait(1)
        ->navigate(route('organizations.index'))
        ->assertSee('Demo Organization');

    visit(route('dashboard'))
        ->wait(1)
        ->click('[data-test="skip-onboarding"]')
        ->resize(900, 600)
        ->wait(2)
        ->click('[data-test="select-team"]')
        ->wait(1)
        ->click('[data-test="select-option-team-0"]')
        ->wait(1)
        ->click('[data-test="skip-onboarding"]')
        ->wait(1)
        ->click('[data-test="select-team"]')
        ->screenshot(filename: 'organization-team-select');
});
