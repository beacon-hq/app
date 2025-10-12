<?php

declare(strict_types=1);

use App\Actions\Invites\ListInvites;
use App\Enums\Role;
use App\Models\Invite as InviteModel;
use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use App\Values\Collections\InviteCollection;
use Illuminate\Support\Facades\DB;
use Tests\Unit\Actions\AppContext\SetAppContext;

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

    // Create invites for another team (should not be included in results)
    for ($i = 0; $i < 2; $i++) {
        InviteModel::create([
            'email' => "other-team-invite-{$i}@example.com",
            'role' => Role::DEVELOPER,
            'team_id' => $this->anotherTeam->id,
            'organization_id' => $this->organization->id,
            'user_id' => $this->user->id,
            'expires_at' => now()->add(config('beacon.teams.invitation_expiration')),
        ]);
    }

    $this->listInvitesAction = ListInvites::make();
});

afterEach(function () {
    DB::rollBack();
});

it('lists all invites for the current team', function () {
    $result = $this->listInvitesAction->handle();

    expect($result)->toBeInstanceOf(InviteCollection::class)
        ->and($result)->toHaveCount(3);

    // Check that all invites are from the current team
    $emails = $this->teamInvites->pluck('email')->toArray();
    foreach ($result as $invite) {
        expect($invite->email)->toBeIn($emails)
            ->and($invite->team->id)->toBe($this->team->id);
    }
});

it('returns empty collection when there are no invites for the current team', function () {
    // Delete all invites for the current team
    InviteModel::where('team_id', $this->team->id)->delete();

    $result = $this->listInvitesAction->handle();

    expect($result)->toBeInstanceOf(InviteCollection::class)
        ->and($result)->toBeEmpty();
});
