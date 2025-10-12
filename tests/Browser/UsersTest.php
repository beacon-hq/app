<?php

declare(strict_types=1);

use App\Actions\Invites\CreateInvite;
use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\User;
use App\Notifications\RegistrationInvite;
use App\Values\Organization;
use App\Values\Team;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\Unit\Actions\AppContext\SetAppContext;

it('can invite a new user', function () {
    Notification::fake();

    [$team, $user, $organization] = createOwner();

    $this->actingAs($user);

    SetAppContext::run($organization, $team);

    $page = visit(route('users.index'))
        ->resize(3024 / 2, 650)
        ->assertSee('Users')
        ->wait(1)
        ->screenshotElement('[data-test="main"]', 'users-initial');

    $page
        ->click(':nth-match([data-test="button-invite-user"], 1)')
        ->assertSee('Invite New User')
        ->wait(1)
        ->screenshotElement('[data-test="dialog-invite-user"]', 'users-form-invite')
        ->type('[data-test="input-invite-email"]', 'davey@php.net')
        ->click('[data-test="select-invite-role"]')
        ->wait(1)
        ->click('[data-test="select-option-invite-role-admin"]')
        ->wait(1)
        ->click('[data-test="select-invite-team"]')
        ->wait(1)
        ->click('[data-test="select-option-invite-team-0"]')
        ->wait(1)
        ->click('[data-test="button-invite-submit"]')
        ->wait(1)
        ->assertSee('Pending Invites')
        ->wait(3)
        ->assertSee('davey@php.net')
        ->screenshotElement('[data-test="main"]', 'users-invite-list');

    Notification::assertSentTimes(RegistrationInvite::class, 1);
});

it('can accept an invite', function () {
    Config::set('beacon.billing.enabled', false);

    [$team, $user, $organization] = createOwner();

    $invite = CreateInvite::run(
        $user,
        Team::from(id: $team->id),
        Organization::from($organization),
        'davey@php.net',
        Role::DEVELOPER,
    );

    $url = URL::temporarySignedRoute(
        'teams.accept-invite',
        now()->addMinutes(5),
        [
            'id' => $invite->id,
            'team' => $team->id,
        ]
    );

    $this->actingAsGuest();

    visit($url)
        ->assertSee('Register')
        ->click('[data-test="main"]')
        ->screenshotElement('[data-test="card-register"]', 'users-invite-accept')
        ->assertSee('Invitation Accepted')
        ->assertSee('Register below to join the "Beacon Demo\'s Team" team.')
        ->click('[data-test="link-already-registered"]')
        ->assertSee('Need an account?')
        ->click('[data-test="main"]')
        ->screenshotElement('[data-test="card-login"]', 'users-invite-accept-login');
});

it('can edit a user', function () {
    [$team, $user, $organization] = createOwner();

    $this->actingAs($user);

    SetAppContext::run($organization, $team);

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

    $this->actingAs($user);

    visit(route('users.index'))
        ->resize(3024 / 2, 700)
        ->assertSee('Users')
        ->click(':nth-match([data-test="button-user-edit"], 1)')
        ->assertSee('Manage User')
        ->wait(1)
        ->screenshotElement('[data-test="dialog-user-manage"]', 'users-form-edit');
});
