<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\FeatureFlag;
use App\Models\User;

class FeatureFlagPolicy
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
    public function view(User $user, FeatureFlag $featureFlag): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::FEATURE_FLAGS);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, FeatureFlag $featureFlag): bool
    {
        return $user->hasAnyPermission(
            Permission::FEATURE_FLAGS() . '.' . $featureFlag->id,
            Permission::FEATURE_FLAG_STATUS() . '.' . $featureFlag->id,
        );
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FeatureFlag $featureFlag): bool
    {
        return $user->hasPermissionTo(Permission::FEATURE_FLAGS() . '.' . $featureFlag->id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, FeatureFlag $featureFlag): bool
    {
        return $user->hasPermissionTo(Permission::FEATURE_FLAGS() . '.' . $featureFlag->id);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, FeatureFlag $featureFlag): bool
    {
        return $user->hasPermissionTo(Permission::FEATURE_FLAGS() . '.' . $featureFlag->id);
    }
}
