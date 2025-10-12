<?php

declare(strict_types=1);

use App\Actions\Teams\CreateTeam;
use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\User;
use App\Values\Organization;
use App\Values\Team;
use App\Values\User as UserValue;
use Tests\Unit\Actions\AppContext\SetAppContext;

it('can view teams page', function () {
    [$team, $user, $organization] = createOwner();

    $this->actingAs($user);

    SetAppContext::run($organization, $team);

    $page = visit(route('dashboard'))
        ->resize(3024 / 2, 700)
        ->click('[data-test="skip-onboarding"]')
        ->navigate(route('teams.index'))
        ->assertSee('Teams')
        ->screenshotElement('[data-test="main"]', 'teams-initial')
        ->screenshotElement('[data-test="main"]', 'teams-list');
});

it('can navigate to team management', function () {
    [$team, $user, $organization] = createOwner();

    $this->actingAs($user);

    SetAppContext::run($organization, $team);

    visit(route('dashboard'))
        ->resize(3024 / 2, 700)
        ->click('[data-test="skip-onboarding"]')
        ->navigate(route('teams.index'))
        ->assertSee('Teams')
        ->screenshot(filename: 'teams-page');
});

it('can create a new team', function () {
    [$team, $user, $organization] = createOwner();

    $this->actingAs($user);

    SetAppContext::run($organization, $team);

    visit(route('dashboard'))
        ->resize(3024 / 2, 700)
        ->click('[data-test="skip-onboarding"]')
        ->navigate(route('teams.index'))
        ->assertSee('Teams')
        ->click('[data-test="button-teams-create"]')
        ->screenshotElement('[data-test="main"]', 'teams-form-create')
        ->assertSee('New Team')
        ->type('[data-test="input-teams-name"]', 'Demo Team')
        ->hover('.bg-orange-400')
        ->click('.bg-orange-400')
        ->click('[data-test="icon-picker"]')
        ->assertSee('Search for an icon')
        ->type('[data-test="input-icon-picker-search"]', 'anchor')
        ->click('[data-test="icon-picker-icon-0"]')
        ->click('[data-test="button-teams-submit"]')
        ->assertSee('Demo Team')
        ->screenshotElement('[data-test="card-teams"]', 'teams-after-create');
});

it('can edit a team', function () {
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

    $user2->organizations()->sync($organization);
    $user2->assignRole(Role::DEVELOPER());

    visit(route('teams.index'))
        ->resize(3024 / 2, 700)
        ->assertSee('Teams')
        ->screenshotElement('[data-test="main"]', 'teams-list')
        ->click('[data-test="card-teams-team-0"]')
        ->screenshotElement('[data-test="main"]', 'teams-form-edit')
        ->type('[data-test="input-teams-name"]', 'Demo Team Updated')
        ->hover('.bg-fuchsia-400')
        ->click('.bg-fuchsia-400')
        ->click('[data-test="icon-picker"]')
        ->type('[data-test="input-icon-picker-search"]', 'anvil')
        ->click('[data-test="icon-picker-icon-0"]')
        ->click('[data-test="button-teams-submit"]')
        ->assertSee('Demo Team Updated')
        ->screenshotElement('[data-test="main"]', 'teams-after-edit')
        ->click('[data-test="button-add-members"]')
        ->screenshotElement('[data-test="dialog-add-members"]', 'teams-add-members')
        ->click('[data-test="select-teams-team-member"]')
        ->click('[data-test="select-option-teams-team-member-0"]')
        ->click('[data-test="button-teams-add-team-member"]')
        ->screenshotElement('[data-test="dialog-add-members"]', 'teams-add-members-selected')
        ->click('[data-test="button-teams-add-members-submit"]')
        ->screenshotElement('[data-test="main"]', 'teams-after-members-added')
        ->click('[data-test="button-teams-delete-member-0"]')
        ->screenshotElement('[data-test="dialog-teams-delete-member"]', 'teams-delete-member-confirmation');
});

it('can switch teams', function () {
    [$team, $user, $organization] = createOwner();

    $this->actingAs($user);

    SetAppContext::run($organization, $team);

    CreateTeam::run(Team::from(
        organization: Organization::from($organization),
        name: 'Demo Team',
        icon: 'anchor',
        color: 'red',
    ), UserValue::from($user));

    visit(route('dashboard'))
        ->resize(3024 / 2, 700)
        ->click('[data-test="skip-onboarding"]')
        ->resize(800, 600)
        ->click('[data-test="select-team"]')
        ->screenshot(filename: 'teams-select')
        ->click('[data-test="select-option-team-0"]')
        ->assertSeeIn('[data-test="select-team"]', 'Demo Team');
});
