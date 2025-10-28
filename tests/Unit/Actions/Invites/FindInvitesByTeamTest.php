<?php

declare(strict_types=1);

use App\Actions\AppContext\SetAppContext;
use App\Actions\AppContext\SetOrganizationContext;
use App\Actions\AppContext\SetTeamContext;
use App\Actions\Invites\FindInvitesByTeam;
use App\Enums\Role;
use App\Models\Invite as InviteModel;
use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use App\Values\Collections\InviteCollection;
use App\Values\Team as TeamValue;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::beginTransaction();

    $this->organization = Organization::factory()->create();
    $this->team = Team::factory()->for($this->organization)->create();
    $this->anotherTeam = Team::factory()->for($this->organization)->create();
    $this->user = User::factory()->create();

    SetAppContext::run($this->organization, $this->team);

    $this->teamInvites = collect();
    for ($i = 0; $i < 3; $i++) {
        $invite = InviteModel::create([
            'email' => "team-invite-{$i}@example.com",
            'role' => Role::DEVELOPER,
            'team_id' => $this->team->id,
            'organization_id' => $this->organization->id,
            'user_id' => $this->user->id,
            'expires_at' => now()->add(config('beacon.teams.invitation_expiration')),
        ]);
        $this->teamInvites->push($invite);
    }

    // Create invites for another team
    $this->otherTeamInvites = collect();
    for ($i = 0; $i < 2; $i++) {
        $invite = InviteModel::create([
            'email' => "other-team-invite-{$i}@example.com",
            'role' => Role::DEVELOPER,
            'team_id' => $this->anotherTeam->id,
            'organization_id' => $this->organization->id,
            'user_id' => $this->user->id,
            'expires_at' => now()->add(config('beacon.teams.invitation_expiration')),
        ]);
        $this->otherTeamInvites->push($invite);
    }

    $this->findInvitesByTeamAction = FindInvitesByTeam::make();
});

afterEach(function () {
    DB::rollBack();
});

it('finds invites for a specific team', function () {
    $teamValue = TeamValue::from($this->team);
    $result = $this->findInvitesByTeamAction->handle($teamValue);

    expect($result)->toBeInstanceOf(InviteCollection::class)
        ->and($result)->toHaveCount(3);

    // Check that all emails are from the team's invites
    $emails = $this->teamInvites->pluck('email')->toArray();
    foreach ($result as $invite) {
        expect($invite->email)->toBeIn($emails);
    }
});

it('returns empty collection when team has no invites', function () {
    // Create a new team with no invites
    SetOrganizationContext::run($this->organization);

    $emptyTeam = Team::factory()->create(['organization_id' => $this->organization->id]);
    $emptyTeamValue = TeamValue::from($emptyTeam);

    SetTeamContext::run($emptyTeamValue);

    $result = $this->findInvitesByTeamAction->handle($emptyTeamValue);

    expect($result)->toBeInstanceOf(InviteCollection::class)
        ->and($result)->toBeEmpty();
});
