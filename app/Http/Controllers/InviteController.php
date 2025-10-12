<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Invites\DeleteInvite;
use App\Actions\Invites\ResendInvite;
use App\Values\Invite;
use App\Values\User;
use Bag\Attributes\WithoutValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class InviteController
{
    public function destroy(
        #[WithoutValidation]
        Invite $invite
    ): RedirectResponse {
        Gate::authorize('create', User::class);

        DeleteInvite::run($invite);

        return redirect()->route('users.index')
            ->withAlert('success', 'Invitation deleted successfully.');
    }

    public function update(
        #[WithoutValidation]
        Invite $invite
    ): RedirectResponse {
        Gate::authorize('create', User::class);

        ResendInvite::run($invite);

        return redirect()->route('users.index')
            ->withAlert('success', 'Invitation resent successfully.');
    }
}
