<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\AccessToken;
use App\Models\User;
use App\Values\AccessToken as AccessTokenValue;

class AccessTokenPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::API_TOKENS_VIEW());
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AccessToken|AccessTokenValue $personalAccessToken): bool
    {
        return $user->hasPermissionTo(Permission::API_TOKENS_VIEW() . '.' . $personalAccessToken->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::API_TOKENS_CREATE());
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AccessToken|AccessTokenValue $accessToken): bool
    {
        return $user->hasPermissionTo(Permission::API_TOKENS_UPDATE() . '.' . $accessToken->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AccessToken|AccessTokenValue $accessToken): bool
    {
        return $user->hasPermissionTo(Permission::API_TOKENS_DELETE() . '.' . $accessToken->id);
    }
}
