<?php

declare(strict_types=1);

use Laravel\Dusk\Browser;

it('can create new policy', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->loginAs($user)
            ->visitRoute('policies.index')
            ->waitForText('Policies')
            ->screenshotElement('@main', 'policies-initial');

        $browser
            ->click('@button-new-policy')
            ->waitForText('New Policy')
            ->screenshotElement('@sheet-policy-form', 'policies-form-create');

        $browser
            ->type('@input-policy-name', 'Demo Policy')
            ->type('@input-policy-description', 'This is a demo policy.')
            ->screenshot('policies-form-create-fill')
            ->click('@button-policy-submit')
            ->waitForText('Demo Policy', 10)
            ->screenshotElement('@main', 'policies-after-create');
    });
});

it('can view policies list', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->loginAs($user)
            ->visitRoute('policies.index')
            ->waitForText('Policies')
            ->screenshotElement('@main', 'policies-list');
    });
});

it('can edit policies', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->loginAs($user)
            ->visitRoute('policies.index')
            ->waitForText('Policies');

        if ($browser->element('@card-policy')) {
            $browser
                ->click('@card-policy')
                ->waitForText('Edit Policy')
                ->screenshotElement('@main', 'policies-edit');

            $browser
                ->type('@input-policy-description', 'This is an updated demo policy.')
                ->screenshot('policies-form-edit-fill')
                ->click('@button-policy-submit')
                ->waitForText('Policy updated successfully')
                ->screenshotElement('@main', 'policies-after-edit');
        }
    });
});
