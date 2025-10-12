<?php

declare(strict_types=1);

namespace App\Actions\Invites;

use App\Actions\Traits\AsAction;
use App\Models\Invite as InviteModel;
use App\Values\Collections\InviteCollection;
use App\Values\Invite;

class ListInvites
{
    use AsAction;

    public function handle(): InviteCollection
    {
        $invites = InviteModel::query()->get();

        return Invite::collect($invites);
    }
}
