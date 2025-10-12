<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Actions\Traits\AsAction;
use App\Models\Scopes\CurrentTeamScope;
use App\Models\User;
use App\Values\User as UserValue;

class UpdateUser
{
    use AsAction;

    public function handle(UserValue $user): UserValue
    {
        $userModel = User::withoutGlobalScopes([CurrentTeamScope::class])->findOrFail($user->id);
        $userModel->update($user->toCollection()->only('status')->toArray());

        return UserValue::from($userModel);
    }
}
