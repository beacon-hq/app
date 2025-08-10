<?php

declare(strict_types=1);

use Laravel\Dusk\Browser;
use Tests\Browser\Components\ColorPicker;
use Tests\Browser\Components\IconPicker;

afterEach(fn () => $this->browse(function (Browser $browser) {
    $browser->logout();
}));

it('can create new organization', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 600);

        $browser
            ->loginAs($user)
            ->visitRoute('organizations.index')
            ->waitForText('Organizations')
            ->screenshotElement('@main', 'organizations-initial');

        $browser
            ->resize(3024 / 2, 700)
            ->click('@button-new-organization')
            ->waitForText('New Organization')
            ->screenshotElement('@main', 'organizations-form-create');

        $browser
            ->pause(1000)
            ->refresh()
            ->pause(500)
            ->click('@button-new-organization')
            ->pause(500)
            ->type('@input-organization-name', 'Demo Organization')
            ->type('@input-team-name', 'Demo Team')
            ->within(new IconPicker(), function (Browser $browser) {
                $browser->selectIcon('flag', 0);
            })
            ->within(new ColorPicker(), function (Browser $browser) {
                $browser->selectColor('blue');
            })
            ->screenshot('organization-form-create-fill')
            ->click('@button-organization-submit')
            ->pause(1000)
            ->visitRoute('organizations.index')
            ->waitForText('Demo Organization')
            ->screenshotElement('@card-organization', 'organizations-after-create')
            ->screenshotElement('@main', 'organizations-list');
    });
});

it('can view organizations list', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->loginAs($user)
            ->visitRoute('organizations.index')
            ->waitForText('Organizations');
    });
});

it('can edit organizations', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 800);

        $browser
            ->loginAs($user)
            ->visitRoute('organizations.index')
            ->waitForText('Organizations');

        $browser
            ->click('@card-organization')
            ->resize(3024 / 2, 900)
            ->waitForText('Delete Organization')
            ->pause(500)
            ->screenshotElement('@card-organizations-edit', 'organizations-edit');

        $browser
            ->type('@input-organization-name', 'Updated Demo Organization')
            ->click('@button-organization-submit')
            ->waitForText('Updated Demo Organization')
            ->screenshotElement('@main', 'organizations-after-edit');
    });
});

it('can delete organization', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 600);

        $browser
            ->loginAs($user)
            ->visitRoute('organizations.index')
            ->waitForText('Organizations');

        $browser
            ->resize(3024 / 2, 700)
            ->click('@button-new-organization')
            ->waitForText('New Organization');

        $browser
            ->pause(1000)
            ->refresh()
            ->pause(500)
            ->click('@button-new-organization')
            ->pause(500)
            ->type('@input-organization-name', 'Demo Organization')
            ->type('@input-team-name', 'Demo Team')
            ->within(new IconPicker(), function (Browser $browser) {
                $browser->selectIcon('flag', 0);
            })
            ->within(new ColorPicker(), function (Browser $browser) {
                $browser->selectColor('blue');
            })
            ->click('@button-organization-submit')
            ->pause(1000)
            ->visitRoute('organizations.index')
            ->waitForText('Demo Organization');

        $browser
            ->click('@card-organization')
            ->resize(3024 / 2, 900)
            ->waitForText('Delete Organization')
            ->pause(500)
            ->screenshotElement('@card-organizations-delete', 'organizations-delete');

        $browser
            ->click('@button-organizations-delete')
            ->screenshotElement('@alert-organization-delete', 'organizations-confirm-delete');
    });
});

it('is prompted to choose an organization and team on login', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->loginAs($user)
            ->visitRoute('organizations.index')
            ->waitForText('Organizations');

        $browser
            ->click('@button-new-organization')
            ->waitForText('New Organization');

        $browser
            ->pause(1000)
            ->refresh()
            ->pause(500)
            ->click('@button-new-organization')
            ->pause(500)
            ->type('@input-organization-name', 'Demo Organization')
            ->type('@input-team-name', 'Demo Team')
            ->within(new IconPicker(), function (Browser $browser) {
                $browser->selectIcon('flag', 0);
            })
            ->within(new ColorPicker(), function (Browser $browser) {
                $browser->selectColor('blue');
            })
            ->click('@button-organization-submit')
            ->pause(1000)
            ->visitRoute('organizations.index')
            ->waitForText('Demo Organization');

        $browser->logout();
        $browser->loginAs($user);

        $browser
            ->resize(600, 730)
            ->addCookie('hide-menu-bar', 1)
            ->visitRoute('teams.select')
            ->pause(500)
            ->screenshotElement('@main', 'login-select-team');
    });
});

it('can switch organization and team', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 600);

        $browser
            ->loginAs($user)
            ->visitRoute('organizations.index')
            ->waitForText('Organizations');

        $browser
            ->resize(3024 / 2, 700)
            ->click('@button-new-organization')
            ->waitForText('New Organization');

        $browser
            ->pause(1000)
            ->refresh()
            ->pause(500)
            ->click('@button-new-organization')
            ->pause(500)
            ->type('@input-organization-name', 'Demo Organization')
            ->type('@input-team-name', 'Demo Team')
            ->within(new IconPicker(), function (Browser $browser) {
                $browser->selectIcon('flag', 0);
            })
            ->within(new ColorPicker(), function (Browser $browser) {
                $browser->selectColor('blue');
            })
            ->click('@button-organization-submit')
            ->pause(1000)
            ->visitRoute('organizations.index')
            ->waitForText('Demo Organization');

        $browser
            ->visitRoute('dashboard')
            ->pause(500)
            ->click('@skip-onboarding')
            ->resize(900, 600)
            ->pause(2000)
            ->click('@select-team')
            ->pause(500)
            ->click('@select-option-team-0')
            ->pause(1000)
            ->click('@skip-onboarding')
            ->pause(500)
            ->click('@select-team')
            ->screenshot('organization-team-select');
    });
});
