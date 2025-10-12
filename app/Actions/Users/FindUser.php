<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Actions\Traits\AsAction;
use App\Models\Scopes\CurrentTeamScope;
use App\Models\User;
use App\Values\User as UserValue;

class FindUser
{
    use AsAction;

    public function handle(int $id): UserValue
    {
        $user = User::withoutGlobalScopes([CurrentTeamScope::class])->findOrFail($id);

        return UserValue::from($user);
    }
}
