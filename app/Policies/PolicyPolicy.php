<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Policy;
use App\Models\User;
use App\Values\Policy as PolicyValue;

class PolicyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::POLICIES_VIEW());
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Policy|PolicyValue $policy): bool
    {
        return $user->hasPermissionTo(Permission::POLICIES_VIEW() . '.' . $policy->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::POLICIES_CREATE());
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Policy|PolicyValue $policy): bool
    {
        return $user->hasPermissionTo(Permission::POLICIES_UPDATE() . '.' . $policy->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Policy|PolicyValue $policy): bool
    {
        return $user->hasPermissionTo(Permission::POLICIES_DELETE() . '.' . $policy->id);
    }
}
