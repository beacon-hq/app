<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Role;
use App\Repositories\UserRepository;
use App\Values\Team;
use App\Values\User as UserValue;

class UserService
{
    public function __construct(protected UserRepository $userRepository)
    {
    }

    public function addTeam(UserValue $user, Team $team): UserValue
    {
        return $this->userRepository->addTeam($user, $team);
    }

    public function findByEmail(string $email): UserValue
    {
        return $this->userRepository->findByEmail($email);
    }

    public function assignRole(UserValue $user, ?Role $role)
    {
        $this->userRepository->assignRole($user, $role);
    }
}
