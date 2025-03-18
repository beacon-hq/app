<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;
use App\Values\FeatureFlag;

class FeatureFlagPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::FEATURE_FLAGS_VIEW());
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, FeatureFlag $featureFlag): bool
    {
        return $user->hasPermissionTo(Permission::FEATURE_FLAGS_VIEW() . '.' . $featureFlag->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::FEATURE_FLAGS_CREATE());
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, FeatureFlag $featureFlag): bool
    {
        return $user->hasAnyPermission(
            Permission::FEATURE_FLAGS_UPDATE() . '.' . $featureFlag->id,
            Permission::FEATURE_FLAG_STATUS_UPDATE() . '.' . $featureFlag->id,
        );
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FeatureFlag $featureFlag): bool
    {
        return $user->hasPermissionTo(Permission::FEATURE_FLAGS_DELETE() . '.' . $featureFlag->id);
    }
}
