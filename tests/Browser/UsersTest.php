<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\Invite;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Laravel\Dusk\Browser;

afterEach(fn () => $this->browse(function (Browser $browser) {
    $browser->logout();
}));

it('can invite a new user', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 650);

        $browser
            ->loginAs($user)
            ->visitRoute('users.index')
            ->waitForText('Users')
            ->pause(300)
            ->screenshotElement('@main', 'users-initial');

        $browser
            ->click('@button-invite-user')
            ->waitForText('Invite New User')
            ->pause(300)
            ->screenshotElement('@dialog-invite-user', 'users-form-invite');

        $browser
            ->type('@input-invite-email', 'davey@php.net')
            ->click('@select-invite-role')
            ->pause(100)
            ->click('@select-option-invite-role-admin')
            ->pause(100)
            ->click('@select-invite-team')
            ->pause(100)
            ->click('@select-option-invite-team-0')
            ->pause(100)
            ->click('@button-invite-submit');

        $browser
            ->waitForText('Pending Invites')
            ->pause(3000)
            ->assertSee('davey@php.net')
            ->screenshotElement('@main', 'users-invite-list');

        $invite = Invite::firstWhere('user_id', $user->id);

        $url = URL::temporarySignedRoute(
            'teams.accept-invite',
            now()->addMinutes(5),
            [
                'id' => $invite->id,
                'team' => $invite->team->id,
            ]
        );

        $browser
            ->resize(3024 / 2, 1400)
            ->deleteCookie('beacon_session')
            ->visit($url)
            ->waitForText('Register', 30)
            ->pause(300)
            ->click('@main')
            ->screenshotElement('@card-register', 'users-invite-accept')
            ->assertSee('Invitation Accepted')
            ->assertSee('Register below to join the "Beacon Demo\'s Team" team.')
            ->click('@link-already-registered')
            ->pause(1000)
            ->assertSee('Need an account?')
            ->pause(300)
            ->click('@main')
            ->screenshotElement('@card-login', 'users-invite-accept-login');
    });
});

it('can edit a user', function () {
    $user = createBrowserUser();

    App::context(organization: $user->organizations->first());

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

    \DB::commit();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->loginAs($user)
            ->visitRoute('users.index')
            ->waitForText('Users');

        $browser
            ->click('@button-user-edit')
            ->waitForText('Manage User')
            ->pause(300)
            ->screenshotElement('@dialog-user-manage', 'users-form-edit');
    });
});
