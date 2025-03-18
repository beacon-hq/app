<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;
use App\Values\FeatureFlagStatus;

class FeatureFlagStatusPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(Permission::FEATURE_FLAGS_VIEW(), Permission::FEATURE_FLAG_STATUS_VIEW());
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, FeatureFlagStatus $featureFlagStatus): bool
    {
        return $user->hasAnyPermission(Permission::FEATURE_FLAGS_VIEW(), Permission::FEATURE_FLAG_STATUS_VIEW() . '.' . $featureFlagStatus->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission(
            Permission::FEATURE_FLAGS_CREATE(),
            Permission::FEATURE_FLAG_STATUS_CREATE(),
        );
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, FeatureFlagStatus $featureFlagStatus): bool
    {
        return $user->hasAnyPermission(
            Permission::FEATURE_FLAGS_UPDATE() . '.' . $featureFlagStatus->id,
            Permission::FEATURE_FLAG_STATUS_UPDATE() . '.' . $featureFlagStatus->id,
        );
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FeatureFlagStatus $featureFlagStatus): bool
    {
        return $user->hasAnyPermission(
            Permission::FEATURE_FLAGS_DELETE() . '.' . $featureFlagStatus->id,
            Permission::FEATURE_FLAG_STATUS_DELETE() . '.' . $featureFlagStatus->id,
        );
    }
}
