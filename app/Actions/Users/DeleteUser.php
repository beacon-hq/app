<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Actions\Traits\AsAction;
use App\Models\Scopes\CurrentTeamScope;
use App\Models\User;
use App\Values\User as UserValue;

class DeleteUser
{
    use AsAction;

    public function handle(UserValue $user): void
    {
        $userModel = User::withoutGlobalScopes([CurrentTeamScope::class])->findOrFail($user->id);

        // Remove Roles
        $userModel->syncRoles([]);

        $userModel->delete();
    }
}
