<?php

declare(strict_types=1);

use App\Actions\Invites\DeleteInvite;
use App\Enums\Role;
use App\Models\Invite as InviteModel;
use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use App\Values\Invite;
use Illuminate\Support\Facades\DB;
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
        'expires_at' => now()->add(config('beacon.teams.invitation_expiration')),
    ]);

    $this->deleteInviteAction = DeleteInvite::make();
});

afterEach(function () {
    DB::rollBack();
});

it('deletes an invite', function () {
    // Convert model to value object
    $invite = Invite::from($this->invite);

    // Execute the action
    $this->deleteInviteAction->handle($invite);

    // Verify the invite is deleted
    $found = InviteModel::find($this->invite->id);
    expect($found)->toBeNull();
});
