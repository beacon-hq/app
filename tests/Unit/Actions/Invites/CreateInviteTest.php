<?php

declare(strict_types=1);

use App\Actions\AppContext\SetAppContext;
use App\Actions\AppContext\SetOrganizationContext;
use App\Actions\AppContext\SetTeamContext;
use App\Actions\Invites\CreateInvite;
use App\Enums\Role;
use App\Models\Invite;
use App\Models\Organization;
use App\Models\Team;
use App\Notifications\RegistrationInvite;
use App\Values\AppContext;
use App\Values\Organization as OrganizationValue;
use App\Values\Team as TeamValue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    DB::beginTransaction();

    [$this->team, $this->user, $this->organization] = createOwner();

    SetAppContext::run(null, $this->team);

    $this->createInviteAction = CreateInvite::make();
});

afterEach(function () {
    DB::rollBack();
});

it('creates an invite and sends an invitation email', function () {
    Notification::fake();

    $email = 'test@example.com';
    $role = Role::DEVELOPER;

    $result = $this->createInviteAction->handle(
        $this->user,
        TeamValue::from($this->team),
        OrganizationValue::from($this->organization),
        $email,
        $role
    );

    // Check that the invite was created in the database
    $inviteModel = Invite::where('email', $email)->first();
    expect($inviteModel)->not->toBeNull()
        ->and($inviteModel->email)->toBe($email)
        ->and($inviteModel->role)->toBe($role)
        ->and($inviteModel->team_id)->toBe($this->team->id)
        ->and($inviteModel->organization_id)->toBe($this->organization->id)
        ->and($inviteModel->user_id)->toBe($this->user->id)
        ->and($inviteModel->expires_at)->not->toBeNull();

    // Check that the result is as expected
    expect($result->email)->toBe($email)
        ->and($result->role)->toBe($role)
        ->and($result->team->id)->toBe($this->team->id)
        ->and($result->organization->id)->toBe($this->organization->id)
        ->and($result->user->id)->toBe($this->user->id)
        ->and($result->expires_at)->not->toBeNull();

    // Check that the notification was sent
    Notification::assertSentTo($this->user, RegistrationInvite::class);
});

it('throws validation exception when email already invited to the organization', function () {
    $email = 'duplicate@example.com';
    $role = Role::DEVELOPER;

    // Create an invite with the same email for the same organization
    Invite::create([
        'email' => $email,
        'role' => $role,
        'team_id' => $this->team->id,
        'organization_id' => $this->organization->id,
        'user_id' => $this->user->id,
        'expires_at' => now()->add(config('beacon.teams.invitation_expiration')),
    ]);

    // Attempt to create another invite with the same email
    $this->createInviteAction->handle(
        $this->user,
        TeamValue::from($this->team),
        OrganizationValue::from($this->organization),
        $email,
        $role
    );
})->throws(ValidationException::class, 'This email has already been invited to this organization.');

it('allows the same email to be invited to different organizations', function () {
    $email = 'multi-org@example.com';
    $role = Role::DEVELOPER;

    // Create an invite for the first organization
    $this->createInviteAction->handle(
        $this->user,
        TeamValue::from($this->team),
        OrganizationValue::from($this->organization),
        $email,
        $role
    );

    // Create a different organization and team
    $this->app->singleton(AppContext::class, fn () => AppContext::from([]));
    $anotherOrganization = Organization::factory(['owner_id' => $this->user->id])->create();
    SetOrganizationContext::run($anotherOrganization);

    $anotherTeam = Team::factory()->createQuietly(['organization_id' => $anotherOrganization->id]);
    SetTeamContext::run($anotherTeam);

    // This should not throw an exception - different organization
    $result = $this->createInviteAction->handle(
        $this->user,
        TeamValue::from($anotherTeam),
        OrganizationValue::from($anotherOrganization),
        $email,
        $role
    );

    expect($result)->not->toBeNull()
        ->and($result->organization->id)->toBe($anotherOrganization->id);

    // Verify we have two invites with the same email
    $count = Invite::withoutGlobalScopes()->where('email', $email)->count();
    expect($count)->toBe(2);
});
