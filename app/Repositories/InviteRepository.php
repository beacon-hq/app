<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\Role;
use App\Models\Invite;
use App\Values\Collections\InviteCollection;
use App\Values\Invite as InviteValue;
use App\Values\Team;
use App\Values\User;

class InviteRepository
{
    public function findByTeam(Team $team): InviteCollection
    {
        return InviteValue::collect(
            Invite::query()
                ->where('team_id', $team->id)
                ->get()
        );
    }

    public function create(User $user, Team $team, Role $role, string $email): InviteValue
    {
        return InviteValue::from(
            Invite::create([
                'email' => $email,
                'role' => $role,
                'team_id' => $team->id,
                'user_id' => $user->id,
                'expires_at' => now()->add(config('beacon.teams.invitation_expiration')),
            ])
        );
    }

    public function findById(string $inviteId): InviteValue
    {
        return InviteValue::from(
            Invite::findOrFail($inviteId)
        );
    }

    public function delete(InviteValue $invite)
    {
        Invite::destroy($invite->id);
    }
}
