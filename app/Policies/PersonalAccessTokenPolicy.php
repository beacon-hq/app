<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\PersonalAccessToken;
use App\Models\User;

class PersonalAccessTokenPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::API_TOKENS);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PersonalAccessToken $personalAccessToken): bool
    {
        return $user->hasPermissionTo(Permission::API_TOKENS() . '.' . $personalAccessToken->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::API_TOKENS);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PersonalAccessToken $personalAccessToken): bool
    {
        return $user->hasPermissionTo(Permission::API_TOKENS() . '.' . $personalAccessToken->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PersonalAccessToken $personalAccessToken): bool
    {
        return $user->hasPermissionTo(Permission::API_TOKENS() . '.' . $personalAccessToken->id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PersonalAccessToken $personalAccessToken): bool
    {
        return $user->hasPermissionTo(Permission::API_TOKENS() . '.' . $personalAccessToken->id);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PersonalAccessToken $personalAccessToken): bool
    {
        return $user->hasPermissionTo(Permission::API_TOKENS() . '.' . $personalAccessToken->id);
    }
}
