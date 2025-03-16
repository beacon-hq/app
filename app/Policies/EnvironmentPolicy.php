<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Environment;
use App\Models\User;

class EnvironmentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Environment $environment): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ENVIRONMENTS);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Environment $environment): bool
    {
        return $user->hasPermissionTo(Permission::ENVIRONMENTS() . '.' . $environment->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Environment $environment): bool
    {
        return $user->hasPermissionTo(Permission::ENVIRONMENTS() . '.' . $environment->id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Environment $environment): bool
    {
        return $user->hasPermissionTo(Permission::ENVIRONMENTS() . '.' . $environment->id);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Environment $environment): bool
    {
        return $user->hasPermissionTo(Permission::ENVIRONMENTS() . '.' . $environment->id);
    }
}
