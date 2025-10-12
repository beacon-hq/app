<?php

declare(strict_types=1);

namespace App\Actions\Invites;

use App\Actions\Traits\AsAction;
use App\Models\Invite as InviteModel;
use App\Values\Invite;

class FindInviteById
{
    use AsAction;

    public function handle(string $inviteId): Invite
    {
        $invite = InviteModel::findOrFail($inviteId);

        return Invite::from($invite);
    }
}
