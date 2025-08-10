<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\Role;
use App\Models\Invite;
use App\Models\Scopes\CurrentTeamScope;
use App\Values\Invite as InviteValue;
use App\Values\Organization;
use App\Values\Team;
use App\Values\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class InviteRepository
{
    public function findByTeam(Team $team): Collection
    {
        return Invite::query()
            ->where('team_id', $team->id)
            ->get();
    }

    public function create(User $user, Team $team, Organization $organization, Role $role, string $email): Invite
    {
        Validator::validate([
            'email' => $email,
        ], [
            'email' => [Rule::unique('invites')->where(function ($query) use ($organization) {
                return $query->where('organization_id', $organization->id);
            })],
        ], [
            'email.unique' => __('This email has already been invited to this organization.'),
        ]);

        return Invite::create([
            'email' => $email,
            'role' => $role,
            'team_id' => $team->id,
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'expires_at' => now()->add(config('beacon.teams.invitation_expiration')),
        ]);
    }

    public function findById(string $inviteId): Invite
    {
        return Invite::findOrFail($inviteId);
    }

    public function delete(InviteValue $invite): void
    {
        Invite::query()->withoutGlobalScope(CurrentTeamScope::class)->find($invite->id)->forceDelete();
    }

    public function findTeamInvite(Team $team, string $email): Invite
    {
        return Invite::where('team_id', $team->id)
            ->where('email', $email)
            ->firstOrFail();
    }

    public function all(): Collection
    {
        return Invite::query()
            ->get();
    }

    public function refreshExpiration(InviteValue $invite): Invite
    {
        $model = Invite::withoutGlobalScope(CurrentTeamScope::class)->findOrFail($invite->id);
        $model->update([
            'expires_at' => now()->add(config('beacon.teams.invitation_expiration')),
        ]);

        return $model->fresh();
    }
}
