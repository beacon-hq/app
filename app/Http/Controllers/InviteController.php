<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\InviteService;
use App\Values\Invite;
use App\Values\User;
use Bag\Attributes\WithoutValidation;
use Gate;
use Illuminate\Http\RedirectResponse;

class InviteController extends Controller
{
    public function __construct(
        protected InviteService $inviteService,
    ) {
    }

    public function destroy(
        #[WithoutValidation]
        Invite $invite
    ): RedirectResponse {
        Gate::authorize('create', User::class);

        $this->inviteService->delete($invite);

        return redirect()->route('users.index')
            ->withAlert('success', 'Invitation deleted successfully.');
    }

    public function update(
        #[WithoutValidation]
        Invite $invite
    ): RedirectResponse {
        Gate::authorize('create', User::class);

        $this->inviteService->resend($invite);

        return redirect()->route('users.index')
            ->withAlert('success', 'Invitation resent successfully.');
    }
}
