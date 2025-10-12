<?php

declare(strict_types=1);

use Tests\Unit\Actions\AppContext\SetAppContext;

it('can view profile page', function () {
    [$team, $user, $organization] = createOwner();

    $this->actingAs($user);

    SetAppContext::run($organization, $team);

    $this->actingAs($user);

    $page = visit(route('profile.edit'))
        ->resize(3024 / 2, 700);

    $page
        ->assertSee('My Account')
        ->screenshotElement('[data-test="main"]', 'profile-initial')
        ->assertSee('Profile Settings')
        ->screenshotElement('[data-test="section-profile-information"]', 'profile-information-section');
});

it('can update profile information', function () {
    $user = createBrowserUser();

    $this->actingAs($user);

    $page = visit(route('profile.edit'))
        ->resize(3024 / 2, 700);

    $page
        ->assertSee('My Account')
        ->type('[data-test="input-first-name"]', 'Updated First')
        ->type('[data-test="input-last-name"]', 'Updated Last')
        ->type('[data-test="input-email"]', 'updated@example.com')
        ->screenshotElement('[data-test="main"]', 'profile-form-filled')
        ->click('[data-test="button-save-profile"]')
        ->screenshotElement('[data-test="section-profile-information"]', 'profile-after-update');
});

it('can view all profile sections', function () {
    $user = createBrowserUser();

    $this->actingAs($user);

    $page = visit(route('profile.edit'))
        ->resize(3024 / 2, 700);

    $page
        ->assertSee('My Account')
        ->screenshotElement('[data-test="section-profile-information"]', 'profile-information')
        ->screenshotElement('[data-test="section-update-password"]', 'profile-password')
        ->screenshotElement('[data-test="section-two-factor"]', 'profile-two-factor')
        ->screenshotElement('[data-test="section-delete-user"]', 'profile-delete-user');
});
