<?php

declare(strict_types=1);

use App\Actions\AppContext\SetTeamContext;
use App\Actions\Invites\FindTeamInvite;
use App\Enums\Role;
use App\Models\Invite as InviteModel;
use App\Models\Team;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Tests\Unit\Actions\AppContext\SetAppContext;

beforeEach(function () {
    DB::beginTransaction();


    [$this->team, $this->user, $this->organization] = createOwner();

    SetAppContext::run($this->organization, $this->team);

    // Create an invite for the team
    $this->email = 'test@example.com';
    $this->invite = InviteModel::create([
        'email' => $this->email,
        'role' => Role::DEVELOPER,
        'team_id' => $this->team->id,
        'organization_id' => $this->organization->id,
        'user_id' => $this->user->id,
        'expires_at' => now()->add(config('beacon.teams.invitation_expiration')),
    ]);

    // Create an invite with a different email for the same team
    $this->otherEmail = 'other@example.com';
    $this->otherInvite = InviteModel::create([
        'email' => $this->otherEmail,
        'role' => Role::DEVELOPER,
        'team_id' => $this->team->id,
        'organization_id' => $this->organization->id,
        'user_id' => $this->user->id,
        'expires_at' => now()->add(config('beacon.teams.invitation_expiration')),
    ]);

    $this->anotherTeam = Team::factory()->for($this->organization)->create();
    SetTeamContext::run($this->anotherTeam);

    // Create an invite with the same email but for a different team
    $this->sameEmailDifferentTeamInvite = InviteModel::create([
        'email' => $this->email,
        'role' => Role::DEVELOPER,
        'team_id' => $this->anotherTeam->id,
        'organization_id' => $this->organization->id,
        'user_id' => $this->user->id,
        'expires_at' => now()->add(config('beacon.teams.invitation_expiration')),
    ]);

    $this->findTeamInviteAction = FindTeamInvite::make();
});

afterEach(function () {
    DB::rollBack();
});

it('finds an invite for a team and email', function () {
    $teamValue = \App\Values\Team::from($this->team);
    $result = $this->findTeamInviteAction->handle($teamValue, $this->email);

    expect($result)->not->toBeNull()
        ->and($result->id)->toBe($this->invite->id)
        ->and($result->email)->toBe($this->email)
        ->and($result->team->id)->toBe($this->team->id);
});

it('throws ModelNotFoundException when invite does not exist for the team', function () {
    $teamValue = \App\Values\Team::from($this->team);
    $this->findTeamInviteAction->handle($teamValue, 'nonexistent@example.com');
})->throws(ModelNotFoundException::class);

it('finds the correct invite when multiple invites exist with same email for different teams', function () {
    // Find invite for first team
    $teamValue = \App\Values\Team::from($this->team);
    $result = $this->findTeamInviteAction->handle($teamValue, $this->email);
    expect($result->id)->toBe($this->invite->id);

    // Find invite for another team
    $anotherTeamValue = \App\Values\Team::from($this->anotherTeam);
    $result = $this->findTeamInviteAction->handle($anotherTeamValue, $this->email);
    expect($result->id)->toBe($this->sameEmailDifferentTeamInvite->id);
});
