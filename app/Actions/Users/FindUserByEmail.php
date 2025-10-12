<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Actions\Traits\AsAction;
use App\Models\Scopes\CurrentTeamScope;
use App\Models\User;
use App\Values\User as UserValue;

class FindUserByEmail
{
    use AsAction;

    public function handle(string $email): UserValue
    {
        $user = User::withoutGlobalScopes([CurrentTeamScope::class])
            ->where('email', $email)
            ->firstOrFail();

        return UserValue::from($user);
    }
}
