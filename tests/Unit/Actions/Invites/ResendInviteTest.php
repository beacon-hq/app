<?php

declare(strict_types=1);

use App\Actions\AppContext\SetOrganizationContext;
use App\Actions\Invites\ResendInvite;
use App\Enums\Role;
use App\Models\Invite as InviteModel;
use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use App\Notifications\RegistrationInvite;
use App\Values\Invite;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\Unit\Actions\AppContext\SetAppContext;

beforeEach(function () {
    DB::beginTransaction();

    $this->organization = Organization::factory()->create();
    $this->team = Team::factory()->for($this->organization)->create();
    $this->user = User::factory()->create();

    SetAppContext::run($this->organization, $this->team);

    $this->invite = InviteModel::create([
        'email' => 'test@example.com',
        'role' => Role::DEVELOPER,
        'team_id' => $this->team->id,
        'organization_id' => $this->organization->id,
        'user_id' => $this->user->id,
        'expires_at' => now()->subDays(1), // Expired invite
    ]);

    $this->resendInviteAction = ResendInvite::make();
});

afterEach(function () {
    DB::rollBack();
});

it('resends an invite and updates its expiration date', function () {
    SetOrganizationContext::run($this->invite->organization);

    Notification::fake();

    $inviteValue = Invite::from($this->invite);
    $originalExpiresAt = $this->invite->expires_at;

    // Execute the action
    $this->resendInviteAction->handle($inviteValue);

    // Refresh the invite from the database
    $this->invite->refresh();

    // Verify that the expiration date has been updated
    expect($this->invite->expires_at->isAfter($originalExpiresAt))->toBeTrue()
        ->and($this->invite->email)->toBe('test@example.com');

    // Verify that a notification was sent
    Notification::assertSentTo(
        $this->invite->user()->withoutGlobalScopes()->first(),
        RegistrationInvite::class
    );
});

it('works with invites from different teams by bypassing the CurrentTeamScope', function () {
    Notification::fake();

    // Create an invite for a different team
    $anotherTeam = Team::factory()->for($this->organization)->create();

    $anotherInvite = InviteModel::create([
        'email' => 'another@example.com',
        'role' => Role::DEVELOPER,
        'team_id' => $anotherTeam->id,
        'organization_id' => $this->organization->id,
        'user_id' => $this->user->id,
        'expires_at' => now()->subDays(1), // Expired invite
    ]);

    $inviteValue = Invite::from($anotherInvite);
    $originalExpiresAt = $anotherInvite->expires_at;

    // Execute the action
    $this->resendInviteAction->handle($inviteValue);

    // Refresh the invite from the database
    $anotherInvite->refresh();

    // Verify that the expiration date has been updated
    expect($anotherInvite->expires_at->isAfter($originalExpiresAt))->toBeTrue();

    // Verify that a notification was sent
    Notification::assertSentTo(
        $this->user,
        RegistrationInvite::class
    );
});

it('throws ModelNotFoundException when invite does not exist', function () {
    // Create a value object with a non-existent ID
    $nonExistentInvite = Invite::from([
        'id' => 'non-existent-id',
        'email' => 'nonexistent@example.com',
        'role' => Role::DEVELOPER,
        'team' => \App\Values\Team::from($this->team),
        'organization' => \App\Values\Organization::from($this->organization),
        'user' => \App\Values\User::from($this->user),
        'expires_at' => now(),
    ]);

    $this->resendInviteAction->handle($nonExistentInvite);
})->throws(ModelNotFoundException::class);
