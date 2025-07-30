<?php

declare(strict_types=1);

use Laravel\Dusk\Browser;
use Tests\Browser\Components\ColorPicker;
use Tests\Browser\Components\IconPicker;

it('can view teams page', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->loginAs($user)
            ->visitRoute('teams.index')
            ->waitForText('Teams')
            ->screenshotElement('@main', 'teams-initial');

        $browser
            ->assertSee('Teams')
            ->screenshotElement('@main', 'teams-list');
    });
});

it('can navigate to team management', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->loginAs($user)
            ->visitRoute('teams.index')
            ->waitForText('Teams')
            ->screenshot('teams-page');
    });
});

it('can create a new team', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->loginAs($user)
            ->visitRoute('teams.index')
            ->waitForText('Teams')
            ->click('@button-teams-create')
            ->screenshotElement('@main', 'teams-form-create')
            ->waitForText('New Team')
            ->type('@input-teams-name', 'Demo Team')
            ->within(new ColorPicker(), function (Browser $browser) {
                $browser->selectColor('orange');
            })
            ->within(new IconPicker(), function (Browser $browser) {
                $browser->selectIcon('anchor', 0);
            })
            ->click('@button-teams-submit')
            ->pause(1000)
            ->refresh()
            ->waitForText('Demo Team')
            ->screenshotElement('@card-teams', 'team-after-create');
    });
});


it('can edit a team', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->loginAs($user)
            ->visitRoute('teams.index')
            ->waitForText('Teams')
            ->screenshotElement('@main', '')
            ->click('@card-teams-team-1')
            ->screenshotElement('@main', 'teams-form-create')
            ->waitForText('New Team')
            ->type('@input-teams-name', 'Demo Team Updated')
            ->within(new ColorPicker(), function (Browser $browser) {
                $browser->selectColor('fuchsia');
            })
            ->within(new IconPicker(), function (Browser $browser) {
                $browser->selectIcon('anvil', 0);
            })
            ->click('@button-teams-submit')
            ->pause(1000)
            ->refresh()
            ->waitForText('Demo Team Updated')
            ->screenshotElement('@main', 'team-after-edit');
    });
});
