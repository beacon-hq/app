<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\FeatureType;
use App\Models\User;

class FeatureTypePolicy
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
    public function view(User $user, FeatureType $featureType): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::FEATURE_TYPES);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, FeatureType $featureType): bool
    {
        return $user->hasPermissionTo(Permission::FEATURE_TYPES() . '.' . $featureType->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FeatureType $featureType): bool
    {
        return $user->hasPermissionTo(Permission::FEATURE_TYPES() . '.' . $featureType->id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, FeatureType $featureType): bool
    {
        return $user->hasPermissionTo(Permission::FEATURE_TYPES() . '.' . $featureType->id);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, FeatureType $featureType): bool
    {
        return $user->hasPermissionTo(Permission::FEATURE_TYPES() . '.' . $featureType->id);
    }
}
