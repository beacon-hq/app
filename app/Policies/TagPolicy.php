<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Tag;
use App\Models\User;
use App\Values\Tag as TagValue;

class TagPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::TAGS_VIEW());
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Tag|TagValue $tag): bool
    {
        return $user->hasPermissionTo(Permission::TAGS_VIEW() . '.' . $tag->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        $user->hasPermissionTo(Permission::TAGS);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Tag|TagValue $tag): bool
    {
        return $user->hasPermissionTo(Permission::TAGS() . '.' . $tag->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Tag|TagValue $tag): bool
    {
        return $user->hasPermissionTo(Permission::TAGS() . '.' . $tag->id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Tag|TagValue $tag): bool
    {
        return $user->hasPermissionTo(Permission::TAGS() . '.' . $tag->id);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Tag|TagValue $tag): bool
    {
        return $user->hasPermissionTo(Permission::TAGS() . '.' . $tag->id);
    }
}
