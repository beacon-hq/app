<?php

declare(strict_types=1);

use Laravel\Dusk\Browser;
use Tests\Browser\Components\ColorPicker;

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
            ->type('#name', 'Demo Application')
            ->type('#description', 'This is a demo application.')
            ->type('#display_name', 'Demo App')
            ->within(new ColorPicker(), function (Browser $browser) {
                $browser->selectColor('lime', '@main', 'applications-form-create-fill');
            })
            ->pause(500)
            ->click('@button-application-submit')
            ->waitForText('Demo Application')
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
            ->waitForText('Demo Application');

        $browser
            ->click('@card-application')
            ->resize(3024 / 2, 900)
            ->waitForText('Edit Application')
            ->pause(500)
            ->screenshotElement('@card-application-edit', 'applications-edit');

        $browser
            ->assertDisabled('#name')
            ->type('#description', 'This is an updated demo application.')
            ->type('#display_name', 'Updated Demo App')
            ->within(new ColorPicker(), function (Browser $browser) {
                $browser->selectColor('purple');
            })
            ->pause(500)
            ->click('@button-application-submit')
            ->waitForText('Updated Demo App')
            ->screenshotElement('@main', 'applications-after-edit')
            ->screenshotElement('@card-application', 'applications-app-card-updated');
    });
});
