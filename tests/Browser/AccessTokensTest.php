<?php

declare(strict_types=1);

use Laravel\Dusk\Browser;

it('can navigate to access tokens', function () {
    $user = createBrowserUser();
    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->loginAs($user)
            ->visitRoute('access-tokens.index')
            ->waitForText('No results.')
            ->screenshotElement('@main', 'access-tokens-initial');
    });
});

it('can create an access token', function () {
    $user = createBrowserUser();
    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->loginAs($user)
            ->visitRoute('access-tokens.index')
            ->waitForText('No results.')
            ->screenshotElement('@main', 'access-tokens-initial');

        $browser
            ->click('@button-access-token-create')
            ->waitForText('New Access Token')
            ->screenshotElement('@dialog-access-token-create', 'access-tokens-form-create');

        $browser
            ->type('#name', 'Demo Access Token')
            ->pause(500)
            ->click('@button-access-token-create-submit')
            ->waitForText('Demo Access Token')
            ->assertSee('Keep this token secure. It won\'t be shown again.')
            ->assertSee('sk_beacon_')
            ->screenshotElement('@main', 'access-tokens-after-create');
    });
});

it('can delete access tokens', function () {
    $user = createBrowserUser();
    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->loginAs($user)
            ->visitRoute('access-tokens.index')
            ->waitForText('Demo Access Token')
            ->screenshotElement('@main', 'access-tokens-before-delete');

        $browser
            ->click('@button-access-token-delete')
            ->waitForText('Delete Token')
            ->assertSee('Are you sure you want to delete the token "Demo Access Token"? This action cannot be undone.')
            ->screenshotElement('@dialog-access-token-delete', 'access-tokens-delete');

        $browser
            ->click('@button-access-token-delete-confirm')
            ->waitForText('No results.')
            ->screenshotElement('@main', 'access-tokens-after-delete');
    });
});
