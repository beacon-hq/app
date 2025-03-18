<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;
use App\Values\FeatureType;

class FeatureTypePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::FEATURE_TYPES_VIEW());
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, FeatureType $featureType): bool
    {
        return $user->hasPermissionTo(Permission::FEATURE_TYPES_VIEW() . '.' . $featureType->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::FEATURE_TYPES_CREATE());
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, FeatureType $featureType): bool
    {
        return $user->hasPermissionTo(Permission::FEATURE_TYPES_UPDATE() . '.' . $featureType->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FeatureType $featureType): bool
    {
        return $user->hasPermissionTo(Permission::FEATURE_TYPES_DELETE() . '.' . $featureType->id);
    }
}
