<?php

declare(strict_types=1);

use App\Actions\Invites\FindInviteById;
use App\Enums\Role;
use App\Models\Invite as InviteModel;
use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

    $this->findInviteByIdAction = FindInviteById::make();
});

afterEach(function () {
    DB::rollBack();
});

it('finds an invite by ID', function () {
    $result = $this->findInviteByIdAction->handle($this->invite->id);

    expect($result)->not->toBeNull()
        ->and($result->id)->toBe($this->invite->id)
        ->and($result->email)->toBe('test@example.com')
        ->and($result->role)->toBe(Role::DEVELOPER)
        ->and($result->team->id)->toBe($this->team->id)
        ->and($result->organization->id)->toBe($this->organization->id)
        ->and($result->user->id)->toBe($this->user->id);
});

it('throws ModelNotFoundException when invite does not exist', function () {
    $this->findInviteByIdAction->handle('non-existent-id');
})->throws(ModelNotFoundException::class);
