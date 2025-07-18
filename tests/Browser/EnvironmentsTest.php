<?php

declare(strict_types=1);

use Laravel\Dusk\Browser;

it('can create a new environment', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->loginAs($user)
            ->visitRoute('environments.index')
            ->screenshotElement('@main', 'environments-initial')
            ->click('@button-create-environment')
            ->screenshotElement('@main', 'environments-form-create')
            ->type('@input-environment-name', 'Testing')
            ->type('@input-environment-description', 'This is a test environment')
            ->mouseover('.bg-sky-400')
            ->screenshotElement('@main', 'environments-form-create-fill')
            ->click('.bg-sky-400')
            ->click('@submit-environment')
            ->waitForText('Testing')
            ->assertSee('This is a test environment')
            ->screenshotElement('@main', 'environments-after-create')
            ->screenshotElement('@card-environment', 'environments-environment-card');
    });
});

it('can edit environments', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->loginAs($user)
            ->visitRoute('environments.index')
            ->waitForText('Testing');

        $browser
            ->click('@card-environment')
            ->waitForText('Edit Environment')
            ->screenshotElement('@main', 'environments-edit');

        $browser
            ->assertDisabled('@input-environment-name')
            ->type('@input-environment-description', 'This is an updated test environment')
            ->click('.bg-purple-400')
            ->pause(500)
            ->click('@submit-environment')
            ->waitForRoute('environments.index')
            ->waitForText('Testing')
            ->assertSee('This is an updated test environment')
            ->screenshotElement('@main', 'environments-after-edit');
    });
});
