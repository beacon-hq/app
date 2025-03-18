<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Team;
use App\Models\User;
use App\Values\Team as TeamValue;

class TeamPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::TEAMS_VIEW());
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Team|TeamValue $team): bool
    {
        return $user->hasPermissionTo(Permission::TEAMS_VIEW() . '.' . $team->id) && $user->withWhereRelation('teams', [
            'team_id' => $team->id,
            'user_id' => $user->id
        ])->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::TEAMS_CREATE());
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Team|TeamValue $team): bool
    {
        return $user->hasPermissionTo(Permission::TEAMS_UPDATE() . '.' . $team->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Team|TeamValue $team): bool
    {
        return $user->hasPermissionTo(Permission::TEAMS_DELETE() . '.' . $team->id);
    }
}
