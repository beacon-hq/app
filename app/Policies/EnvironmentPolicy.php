<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;
use App\Values\Environment;

class EnvironmentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ENVIRONMENTS_VIEW());
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Environment $environment): bool
    {
        return $user->hasPermissionTo(Permission::ENVIRONMENTS_VIEW() . '.' . $environment->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ENVIRONMENTS_CREATE());
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Environment $environment): bool
    {
        return $user->hasPermissionTo(Permission::ENVIRONMENTS_UPDATE() . '.' . $environment->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Environment $environment): bool
    {
        return $user->hasPermissionTo(Permission::ENVIRONMENTS_DELETE() . '.' . $environment->id);
    }
}
