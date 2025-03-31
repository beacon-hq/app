<?php

declare(strict_types=1);

namespace App\Services;

use App;
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
use Session;

class UserService
{
    public function __construct(protected UserRepository $userRepository, protected InviteService $inviteService, protected OrganizationService $organizationService)
    {
    }

    public function all(array $orderBy = ['first_name', 'last_name'], array $filters = [], ?int $page = null, int $perPage = 20): UserCollection
    {
        return $this->userRepository->all(orderBy: $orderBy, filters: $filters, page: $page, perPage: $perPage);
    }

    public function find(int $id): UserValue
    {
        return $this->userRepository->find($id);
    }

    public function update(UserValue $user, array $data): UserValue
    {
        return $this->userRepository->update($user, $data);
    }

    public function delete(UserValue $user): void
    {
        $this->userRepository->delete($user);
    }

    public function addTeam(UserValue $user, Team $team): UserValue
    {
        return $this->userRepository->addTeam($user, $team);
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
        return $this->userRepository->findByEmail($email);
    }

    public function assignRole(UserValue $user, ?Role $role): void
    {
        $this->userRepository->assignRole($user, $role);
    }

    public function teamMembers(Team $team, array|string $orderBy = ['name'], ?int $page = null, int $perPage = 20, array $filters = []): UserCollection
    {
        return $this->userRepository->teamMembers($team, $orderBy, $page, $perPage, $filters);
    }

    public function nonTeamMembers(Team $team): UserCollection
    {
        return $this->userRepository->nonTeamMembers($team);
    }

    public function create(UserValue $user, ?Invite $invite = null): UserValue
    {
        $user = $this->userRepository->create($user, $invite);

        if ($invite !== null) {
            /** @var App\Models\Invite $invite */
            App::context(organization: $invite->team->organization);

            $user = $this->userRepository->addOrganization($user, Organization::from($invite->team->organization));
            $user = $this->userRepository->addTeam($user, Team::from($invite->team));
            $user = $this->userRepository->assignRole($user, $invite->role);

            Session::forget('invite');

            return UserValue::from($user);
        }

        $organization = $this->organizationService->create($user, Organization::from(name: $user->first_name . '\'s Organization'));

        $this->userRepository->addOrganization($user, $organization);

        App::context(organization: $organization);

        $team = Team::from(name: $user->first_name . '\'s Team', color: collect(Color::values())->random());
        $user = $this->userRepository->addTeam(UserValue::from($user), $team);

        $user = $this->userRepository->assignRole($user, Role::OWNER);

        return UserValue::from($user);
    }

    public function sendEmailVerificationNotification(int $id): void
    {
        $this->userRepository->sendEmailVerificationNotification($id);
    }
}
