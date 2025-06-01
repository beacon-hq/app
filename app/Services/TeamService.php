<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\TeamRepository;
use App\Values\Collections\OrganizationCollection;
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
        $teams = $this->teamRepository->all($userId, $includeMembers, $limitToOrganization);

        return Team::collect($teams);
    }

    public function create(Team $team, User $owner): Team
    {
        $createdTeam = $this->teamRepository->create($team);
        $userService = resolve(UserService::class);
        $teamValue = Team::from($createdTeam);
        $userService->addTeam($owner, $teamValue);

        return $teamValue;
    }

    public function update(Team $team): Team
    {
        $updatedTeam = $this->teamRepository->update($team);

        return Team::from($updatedTeam);
    }

    public function find(string $id, OrganizationCollection $organizations): Team
    {
        $team = $this->teamRepository->find($id, $organizations);

        return Team::from($team);
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
