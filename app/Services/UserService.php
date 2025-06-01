<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Color;
use App\Enums\Role;
use App\Repositories\UserRepository;
use App\Values\Collections\TeamCollection;
use App\Values\Collections\UserCollection;
use App\Values\Invite;
use App\Values\Organization;
use App\Values\Team;
use App\Values\User as UserValue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Session;

class UserService
{
    public function __construct(protected UserRepository $userRepository, protected InviteService $inviteService, protected OrganizationService $organizationService, protected AppContextService $appContextService)
    {
    }

    public function all(array $orderBy = ['first_name', 'last_name'], array $filters = [], ?int $page = null, int $perPage = 20): UserCollection
    {
        $users = $this->userRepository->all(orderBy: $orderBy, filters: $filters, page: $page, perPage: $perPage);

        return UserValue::collect($users);
    }

    public function find(int $id): UserValue
    {
        $user = $this->userRepository->find($id);

        return UserValue::from($user);
    }

    public function update(UserValue $user): UserValue
    {
        $updatedUser = $this->userRepository->update($user);

        return UserValue::from($updatedUser);
    }

    public function delete(UserValue $user): void
    {
        $this->userRepository->delete($user);
    }

    public function addTeam(UserValue $user, Team $team): UserValue
    {
        $user = $this->userRepository->addTeam($user, $team);

        return UserValue::from($user);
    }

    public function removeTeam(Team $team, UserValue $user): void
    {
        try {
            if ($user->email !== null && $invite = $this->inviteService->findTeamInvite($team, $user->email)) {
                $this->inviteService->delete($invite);
            }
        } catch (ModelNotFoundException) {
        }

        if ($user->id !== null) {
            $this->userRepository->removeTeam($team, $user);
        }
    }

    public function syncTeams(UserValue $user, TeamCollection $teams): void
    {
        $this->userRepository->syncTeams($user, $teams);
    }

    public function findByEmail(string $email): UserValue
    {
        $user = $this->userRepository->findByEmail($email);

        return UserValue::from($user);
    }

    public function assignRole(UserValue $user, ?Role $role): void
    {
        $this->userRepository->assignRole($user, $role);
    }

    public function teamMembers(Team $team, array|string $orderBy = ['name'], ?int $page = null, int $perPage = 20, array $filters = []): UserCollection
    {
        $users = $this->userRepository->teamMembers($team, $orderBy, $page, $perPage, $filters);

        return UserValue::collect($users);
    }

    public function nonTeamMembers(Team $team): UserCollection
    {
        $users = $this->userRepository->nonTeamMembers($team);

        return UserValue::collect($users);
    }

    public function create(UserValue $user, ?Invite $invite = null): UserValue
    {
        $userModel = $this->userRepository->create($user);
        $user = UserValue::from($userModel);

        if ($invite !== null) {
            $this->appContextService->setOrganization($invite->organization);

            $userModel = $this->userRepository->addOrganization($user, $invite->organization);
            $user = UserValue::from($userModel);

            $userModel = $this->userRepository->addTeam($user, $invite->team);
            $user = UserValue::from($userModel);

            $userModel = $this->userRepository->assignRole($user, $invite->role);
            $user = UserValue::from($userModel);

            $this->inviteService->delete($invite);

            Session::forget('invite');

            return $user;
        }

        $organization = $this->organizationService->create($user, Organization::from(name: $user->firstName . '\'s Organization'));

        $userModel = $this->userRepository->addOrganization($user, $organization);
        $user = UserValue::from($userModel);

        $this->appContextService->setOrganization($organization);

        $team = Team::from(name: $user->firstName . '\'s Team', color: collect(Color::values())->random());
        $userModel = $this->userRepository->addTeam($user, $team);
        $user = UserValue::from($userModel);

        $userModel = $this->userRepository->assignRole($user, Role::OWNER);
        $user = UserValue::from($userModel);

        return $user;
    }

    public function sendEmailVerificationNotification(int $id): void
    {
        $this->userRepository->sendEmailVerificationNotification($id);
    }
}
