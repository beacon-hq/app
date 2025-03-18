<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\Role;
use App\Models\User;
use App\Services\TeamService;
use App\Values\Team;
use App\Values\User as UserValue;

class UserRepository
{
    public function __construct(protected TeamService $teamService)
    {
    }

    public function addTeam(UserValue $user, Team $team): UserValue
    {
        if ($team->id !== null) {
            $team = $this->teamService->findById($team->id);
        } else {
            $team = $this->teamService->create($team);
        }

        $user = User::find($user->id);
        $user->teams()->syncWithoutDetaching($team->id);

        return UserValue::from($user);
    }

    public function findByEmail(string $email): UserValue
    {
        return UserValue::from(User::where('email', $email)->firstOrFail());
    }

    public function assignRole(UserValue $user, ?Role $role)
    {
        $user = User::find($user->id);
        $user->assignRole($role);
    }
}
