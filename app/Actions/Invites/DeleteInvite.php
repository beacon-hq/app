<?php

declare(strict_types=1);

namespace App\Actions\Invites;

use App\Actions\Traits\AsAction;
use App\Models\Invite as InviteModel;
use App\Models\Scopes\CurrentTeamScope;
use App\Values\Invite;

class DeleteInvite
{
    use AsAction;

    public function handle(Invite $invite): void
    {
        InviteModel::query()
            ->withoutGlobalScope(CurrentTeamScope::class)
            ->find($invite->id)
            ->forceDelete();
    }
}
