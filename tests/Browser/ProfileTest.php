<?php

declare(strict_types=1);

use Laravel\Dusk\Browser;

afterEach(fn () => $this->browse(function (Browser $browser) {
    $browser->logout();
}));

it('can view profile page', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->loginAs($user)
            ->visitRoute('profile.edit')
            ->waitForText('My Account')
            ->screenshotElement('@main', 'profile-initial');

        $browser
            ->assertSee('Profile Settings')
            ->screenshotElement('@section-profile-information', 'profile-information-section');
    });
});

it('can update profile information', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->loginAs($user)
            ->visitRoute('profile.edit')
            ->waitForText('My Account');

        $browser
            ->type('@input-first-name', 'Updated First')
            ->type('@input-last-name', 'Updated Last')
            ->type('@input-email', 'updated@example.com')
            ->screenshot('profile-form-filled')
            ->click('@button-save-profile')
            ->pause(1000)
            ->screenshotElement('@section-profile-information', 'profile-after-update');
    });
});

it('can view all profile sections', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->loginAs($user)
            ->visitRoute('profile.edit')
            ->waitForText('My Account');

        $browser
            ->screenshotElement('@section-profile-information', 'profile-information')
            ->screenshotElement('@section-update-password', 'profile-password')
            ->screenshotElement('@section-two-factor', 'profile-two-factor')
            ->screenshotElement('@section-delete-user', 'profile-delete-user');
    });
});
