<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Actions\Traits\AsAction;
use App\Models\Scopes\CurrentTeamScope;
use App\Models\User;

class SendEmailVerificationNotification
{
    use AsAction;

    public function handle(int $id): void
    {
        User::withoutGlobalScopes([CurrentTeamScope::class])
            ->findOrFail($id)
            ->sendEmailVerificationNotification();
    }
}
