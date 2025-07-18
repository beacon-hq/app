<?php

declare(strict_types=1);

use Laravel\Dusk\Browser;

it('can create new application', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->loginAs($user)
            ->visitRoute('applications.index')
            ->waitForText('No Applications')
            ->screenshotElement('@main', 'applications-initial');

        $browser
            ->click('@button-new-application')
            ->waitForText('Application Name')
            ->screenshot('applications-form-create');

        $browser
            ->type('#name', 'Test Application')
            ->type('#description', 'This is a test application.')
            ->type('#display_name', 'Test App')
            ->mouseover('.bg-lime-400')
            ->screenshot('applications-form-create-fill')
            ->click('.bg-lime-400')
            ->pause(500)
            ->click('@button-application-submit')
            ->waitForText('Test Application')
            ->screenshotElement('@main', 'applications-after-create')
            ->screenshotElement('@card-application', 'applications-app-card');
    });
});

it('can edit applications', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->loginAs($user)
            ->visitRoute('applications.index')
            ->waitForText('Test Application');

        $browser
            ->click('@card-application')
            ->waitForText('Edit Application')
            ->screenshotElement('@main', 'applications-edit');

        $browser
            ->assertDisabled('#name')
            ->type('#description', 'This is an updated test application.')
            ->type('#display_name', 'Updated Test App')
            ->click('.bg-purple-400')
            ->pause(500)
            ->click('@button-application-submit')
            ->waitForText('Updated Test App')
            ->screenshotElement('@main', 'applications-after-edit')
            ->screenshotElement('@card-application', 'applications-app-card-updated');
    });
});
