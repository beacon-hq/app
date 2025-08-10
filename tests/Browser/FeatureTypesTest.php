<?php

declare(strict_types=1);

use App\Events\TeamCreatedEvent;
use App\Listeners\InitTeamDataListener;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\ColorPicker;
use Tests\Browser\Components\IconPicker;

afterEach(fn () => $this->browse(function (Browser $browser) {
    $browser->logout();
}));

it('can create new feature type', function () {
    $user = createBrowserUser();

    $init = resolve(InitTeamDataListener::class);
    $init->handle(new TeamCreatedEvent($user->teams()->first()));

    \DB::commit();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->loginAs($user)
            ->visitRoute('feature-types.index')
            ->waitForText('Feature Types')
            ->screenshotElement('@main', 'feature-types-initial');

        $browser
            ->resize(3024 / 2, 800)
            ->click('@button-new-feature-type')
            ->waitForText('New Feature Type')
            ->screenshotElement('@main', 'feature-types-form-create');

        $browser
            ->pause(1000)
            ->refresh()
            ->pause(500)
            ->click('@button-new-feature-type')
            ->pause(500)
            ->within(new IconPicker(), function (Browser $browser) {
                $browser->selectIcon('micro', 0);
            })
            ->type('@input-feature-type-name', 'Demo Feature Type')
            ->type('@input-feature-type-description', 'This is a demo feature type.')
            ->within(new ColorPicker(), function (Browser $browser) {
                $browser->selectColor('fuchsia');
            })
            ->screenshot('feature-types-form-create-fill')
            ->click('@button-feature-type-submit')
            ->waitForText('Demo Feature Type')
            ->screenshotElement('@card-feature-types', 'feature-types-after-create');
    });
});

it('can view feature types list', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->loginAs($user)
            ->visitRoute('feature-types.index')
            ->waitForText('Feature Types')
            ->screenshotElement('@main', 'feature-types-list');
    });
});

it('can edit feature types', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 800);

        $browser
            ->loginAs($user)
            ->visitRoute('feature-types.index')
            ->waitForText('Feature Types');

        $browser
            ->click('@button-feature-type-edit')
            ->waitForText('Edit Feature Type')
            ->pause(500)
            ->screenshotElement('@card-feature-types-edit', 'feature-types-edit');

        $browser
            ->type('@input-feature-type-description', 'This is an updated demo feature type.')
            ->screenshot('feature-types-form-edit-fill')
            ->click('@button-feature-type-submit')
            ->waitForText('Feature Types')
            ->screenshotElement('@main', 'feature-types-after-edit');
    });
});
