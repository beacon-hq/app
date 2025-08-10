<?php

declare(strict_types=1);

use Laravel\Dusk\Browser;

afterEach(fn () => $this->browse(function (Browser $browser) {
    $browser->logout();
}));

it('can login', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(500, 850);

        $browser
            ->visitRoute('login')
            ->addCookie('hide-menu-bar', 1)
            ->addCookie('fortify-testing', 0)
            ->refresh()
            ->waitForText('Forgot your password?')
            ->click('@switch-login-remember')
            ->pause(500)
            ->screenshotElement('@main', 'login-form')
            ->type('email', $user->email)
            ->type('password', 'Password123!')
            ->click('@button-login-submit');

        $browser
            ->waitForText('Verification')
            ->pause(500)
            ->screenshotElement('@main', 'login-verification');
    });
});
