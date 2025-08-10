<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\User;
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
    $this->browse(function (Browser $browser) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->visitRoute('teams.index')
            ->waitForText('Teams')
            ->screenshot('teams-page');
    });
});

it('can create a new team', function () {
    $this->browse(function (Browser $browser) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->visitRoute('teams.index');

        $browser->waitForText('Teams')
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
            ->screenshotElement('@card-teams', 'teams-after-create');
    });
});


it('can edit a team', function () {
    $user = User::latest()->first();

    $user2 = User::createQuietly([
        'first_name' => 'Demo',
        'last_name' => 'User',
        'email' => fake()->unique()->safeEmail(),
        'password' => bcrypt('Password123!'),
        'status' => UserStatus::ACTIVE(),
    ]);

    $user2->forceFill([
        'email_verified_at' => now(),
        'two_factor_confirmed_at' => now(),
        'two_factor_secret' => 'testing',
        'two_factor_recovery_codes' => 'testing',
    ])->save();

    $user2->organizations()->sync($user->organizations()->first());
    $user2->teams()->sync($user->teams()->first());
    $user2->assignRole(Role::DEVELOPER());

    $this->browse(function (Browser $browser) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->visitRoute('teams.index')
            ->waitForText('Teams')
            ->screenshotElement('@main', 'teams-list')
            ->click('@card-teams-team-1')
            ->pause(500)
            ->screenshotElement('@main', 'teams-form-edit')
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
            ->screenshotElement('@main', 'teams-after-edit');

        $browser
            ->click('@button-add-members')
            ->pause(300)
            ->screenshotElement('@dialog-add-members', 'teams-add-members')
            ->click('@select-teams-team-member')
            ->pause(300)
            ->click('@select-option-teams-team-member-0')
            ->pause(300)
            ->click('@button-teams-add-team-member')
            ->pause(300)
            ->screenshotElement('@dialog-add-members', 'teams-add-members-selected')
            ->click('@button-teams-add-members-submit')
            ->pause(1000)
            ->screenshotElement('@main', 'teams-after-members-added');

        $browser
            ->click('@button-teams-delete-member-0')
            ->pause(300)
            ->screenshotElement('@dialog-teams-delete-member', 'teams-delete-member-confirmation');
    });
});

it('can switch teams', function () {
    $this->browse(function (Browser $browser) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->visitRoute('dashboard')
            ->pause(500)
            ->click('@skip-onboarding')
            ->resize(800, 600)
            ->pause(2000)
            ->click('@select-team')
            ->screenshot('teams-select')
            ->click('@select-option-team-0')
            ->pause(1000)
            ->waitForTextIn('@select-team', 'Demo Team');
    });
});
