<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Actions\Traits\AsAction;
use App\Enums\Role;
use App\Models\Scopes\CurrentTeamScope;
use App\Models\User;
use App\Values\User as UserValue;

/**
 * @method static UserValue run(UserValue $user, ?Role $role)
 */
class AssignRoleToUser
{
    use AsAction;

    public function handle(UserValue $user, ?Role $role): UserValue
    {
        $userModel = User::withoutGlobalScopes([CurrentTeamScope::class])->findOrFail($user->id);

        if ($role !== null) {
            $userModel->syncRoles($role);
        } else {
            $userModel->syncRoles([]);
        }

        return UserValue::from($userModel);
    }
}
