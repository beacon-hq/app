<?php

declare(strict_types=1);

namespace App\Actions\Invites;

use App\Actions\Traits\AsAction;
use App\Enums\Role;
use App\Models\Invite as InviteModel;
use App\Models\User;
use App\Notifications\RegistrationInvite;
use App\Values\Invite;
use App\Values\Organization;
use App\Values\Team;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * @method static Invite run(User $user, Team $team, Organization $organization, string $email, Role $role)
 */
class CreateInvite
{
    use AsAction;

    public function handle(User $user, Team $team, Organization $organization, string $email, Role $role): Invite
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

        $inviteModel = InviteModel::create([
            'email' => $email,
            'role' => $role,
            'team_id' => $team->id,
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'expires_at' => now()->add(config('beacon.teams.invitation_expiration')),
        ]);

        $invite = Invite::from($inviteModel);
        Notification::send($user, new RegistrationInvite($invite));

        return $invite;
    }
}
