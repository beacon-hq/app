<?php

declare(strict_types=1);

use App\Models\User;
use Laravel\Dusk\Browser;

afterEach(fn () => $this->browse(function (Browser $browser) {
    $browser->logout();
}));

it('can register a new user', function () {
    $this->browse(function (Browser $browser) {
        $browser->resize(600, 1000);

        $browser
            ->visitRoute('register')
            ->addCookie('hide-menu-bar', 1)
            ->addCookie('fortify-testing', 0)
            ->refresh()
            ->waitForText('Already registered?')
            ->click('.bg-card')
            ->pause(500)
            ->screenshotElement('@main', 'register-form')
            ->type('@input-register-first-name', 'Beacon')
            ->type('@input-register-last-name', 'Demo')
            ->type('@input-register-email', 'support-' . time() . '@beacon-hq.dev')
            ->type('@input-register-password', 'Password123!')
            ->type('@input-register-password-confirmation', 'Password123!')
            ->click('@button-register-submit');

        $browser
            ->waitForLocation('/user/verify-email')
            ->pause(1000)
            ->resize(600, 800)
            ->screenshotElement('@main', 'register-email-confirmation');

        $user = User::latest()->first();
        $user->email_verified_at = now();
        $user->save();

        $browser
            ->resize(600, 1300)
            ->visitRoute('two-factor.setup')
            ->pause(1000)
            ->screenshotElement('@main', 'register-two-factor-setup');

        $browser
            ->resize(600, 1250)
            ->visitRoute('two-factor.confirmed')
            ->pause(1000)
            ->screenshotElement('@main', 'register-two-factor-confirmed');
    });
});
