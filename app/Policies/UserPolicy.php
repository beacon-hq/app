<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;
use App\Values\User as UserValue;
use Auth;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::USERS_VIEW());
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User|UserValue $value): bool
    {
        return $user->hasPermissionTo(Permission::USERS_VIEW() . '.' . $value->id) && $user->id === $value->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        return $user === null || $user->hasPermissionTo(Permission::USERS_CREATE());
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User|UserValue $value): bool
    {
        if ($user->id === $value->id || $user->id === Auth::user()->id) {
            return true;
        }

        if ($value instanceof UserValue && !$value->has('id')) {
            return false;
        }

        return $user->hasPermissionTo(Permission::USERS_UPDATE() . '.' . $value->id) || $user->id === Auth::user()->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User|UserValue $value): bool
    {
        return $user->hasPermissionTo(Permission::USERS() . '.' . $value->id) || $user->id === Auth::user()->id;
    }
}
