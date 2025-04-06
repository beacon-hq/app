<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\TeamRepository;
use App\Values\Collections\TeamCollection;
use App\Values\Collections\UserCollection;
use App\Values\Team;
use App\Values\User;

class TeamService
{
    public function __construct(protected TeamRepository $teamRepository)
    {
    }

    public function all(?int $userId = null, bool $includeMembers = false, bool $limitToOrganization = true): TeamCollection
    {
        return $this->teamRepository->all($userId, $includeMembers, $limitToOrganization);
    }

    public function create(Team $team, User $owner): Team
    {
        $team = $this->teamRepository->create($team);
        $userService = resolve(UserService::class);
        $userService->addTeam($owner, $team);

        return $team;
    }

    public function update(Team $team): Team
    {
        return $this->teamRepository->update($team);
    }

    public function find(string $id): Team
    {
        return $this->teamRepository->find($id);
    }

    public function members(Team $team, array|string $orderBy = ['name'], ?int $page = null, int $perPage = 20, array $filters = []): UserCollection
    {
        return $this->teamRepository->members($team, $orderBy, $page, $perPage, $filters);
    }

    public function nonMembers(Team $team): UserCollection
    {
        return $this->teamRepository->nonMembers($team);
    }
}
