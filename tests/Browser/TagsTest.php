<?php

declare(strict_types=1);

use App\Events\TeamCreatedEvent;
use App\Listeners\InitTeamDataListener;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\ColorPicker;

afterEach(fn () => $this->browse(function (Browser $browser) {
    $browser->logout();
}));

it('can create new tag', function () {
    $user = createBrowserUser();

    $init = resolve(InitTeamDataListener::class);
    $init->handle(new TeamCreatedEvent($user->teams()->first()));

    \DB::commit();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 600);

        $browser
            ->loginAs($user)
            ->visitRoute('tags.index')
            ->waitForText('Tags')
            ->screenshotElement('@main', 'tags-initial');

        $browser
            ->resize(3024 / 2, 800)
            ->click('@button-new-tag')
            ->waitForText('New Tag')
            ->screenshotElement('@main', 'tags-form-create');

        $browser
            ->pause(1000)
            ->refresh()
            ->pause(500)
            ->click('@button-new-tag')
            ->pause(500)
            ->type('@input-tags-name', 'Demo Tag')
            ->type('@input-tags-description', 'This is a demo tag.')
            ->within(new ColorPicker(), function (Browser $browser) {
                $browser->selectColor('lime');
            })
            ->screenshot('tags-form-create-fill')
            ->click('@button-tag-submit')
            ->waitForText('Demo Tag')
            ->screenshotElement('@card-tags', 'tags-after-create');
    });
});

it('can view tags list', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->loginAs($user)
            ->visitRoute('tags.index')
            ->waitForText('Tags')
            ->screenshotElement('@main', 'tags-list');
    });
});

it('can edit tags', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 800);

        $browser
            ->loginAs($user)
            ->visitRoute('tags.index')
            ->waitForText('Tags');

        $browser
            ->click('@button-tags-edit')
            ->waitForText('Edit Tag')
            ->pause(500)
            ->screenshotElement('@card-tags-edit', 'tags-edit');

        $browser
            ->type('@input-tags-description', 'This is an updated demo tag.')
            ->screenshot('tags-form-edit-fill')
            ->click('@button-tag-submit')
            ->waitForText('Tags')
            ->screenshotElement('@main', 'tags-after-edit');
    });
});
