<?php

declare(strict_types=1);

use Laravel\Dusk\Browser;

afterEach(fn () => $this->browse(function (Browser $browser) {
    $browser->logout();
}));

it('skips onboarding', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 1964 / 2);

        $browser
            ->loginAs($user)
            ->visitRoute('dashboard')
            ->waitForText('Get Started');

        $browser
            ->click('@skip-onboarding')
            ->pause(500)
            ->refresh()
            ->assertDontSee('Get Started');
    });
});
