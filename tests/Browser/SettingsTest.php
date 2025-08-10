<?php

declare(strict_types=1);

use Laravel\Dusk\Browser;

afterEach(fn () => $this->browse(function (Browser $browser) {
    $browser->logout();
}));

it('can view settings', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 600);

        $browser
            ->loginAs($user)
            ->visitRoute('settings.index')
            ->waitForText('Settings')
            ->screenshotElement('@main', 'settings-initial');
    });
});
