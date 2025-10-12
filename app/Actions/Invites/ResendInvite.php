<?php

declare(strict_types=1);

namespace App\Actions\Invites;

use App\Actions\Traits\AsAction;
use App\Models\Invite as InviteModel;
use App\Models\Scopes\CurrentTeamScope;
use App\Notifications\RegistrationInvite;
use App\Values\Invite;
use Illuminate\Support\Facades\Notification;

class ResendInvite
{
    use AsAction;

    public function handle(Invite $invite): void
    {
        // Refreshing the invitation expiration time
        $model = InviteModel::withoutGlobalScope(CurrentTeamScope::class)
            ->findOrFail($invite->id);

        $model->update([
            'expires_at' => now()->add(config('beacon.teams.invitation_expiration')),
        ]);

        $invite = Invite::from($model->fresh());

        // Resend the invitation email
        Notification::send($model->user()->withoutGlobalScopes()->first(), new RegistrationInvite($invite));
    }
}
