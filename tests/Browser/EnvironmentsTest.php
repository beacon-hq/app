<?php

declare(strict_types=1);

use Laravel\Dusk\Browser;
use Tests\Browser\Components\ColorPicker;

afterEach(fn () => $this->browse(function (Browser $browser) {
    $browser->logout();
}));

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
            ->type('@input-environment-name', 'Demo')
            ->type('@input-environment-description', 'This is a demo environment')
            ->within(new ColorPicker(), function (Browser $browser) {
                $browser->selectColor('sky', '@main', 'environments-form-create-fill');
            })
            ->click('@submit-environment')
            ->waitForText('Demo')
            ->assertSee('This is a demo environment')
            ->screenshotElement('@main', 'environments-after-create')
            ->refresh()
            ->pause(500)
            ->screenshotElement('.text-card-foreground', 'environments-environment-card');
    });
});

it('can edit environments', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->loginAs($user)
            ->visitRoute('environments.index')
            ->waitForText('Environments');

        $browser
            ->click('@card-environment')
            ->waitForText('Edit Environment')
            ->screenshotElement('@card-environment-edit', 'environments-edit');

        $browser
            ->assertDisabled('@input-environment-name')
            ->type('@input-environment-description', 'This is an updated demo environment')
            ->within(new ColorPicker(), function (Browser $browser) {
                $browser->selectColor('purple');
            })
            ->pause(500)
            ->click('@submit-environment')
            ->waitForText('This is an updated demo environment')
            ->screenshotElement('@main', 'environments-after-edit');
    });
});
