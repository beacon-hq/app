<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Role;
use App\Notifications\RegistrationInvite;
use App\Repositories\InviteRepository;
use App\Values\Collections\InviteCollection;
use App\Values\Invite;
use App\Values\Team;
use App\Values\User;
use Illuminate\Support\Facades\Notification;

class InviteService
{
    public function __construct(protected InviteRepository $inviteRepository)
    {
    }

    public function create(User $user, Team $team, string $email, Role $role): Invite
    {
        $invite = $this->inviteRepository->create($user, $team, $role, $email);
        Notification::send($invite, new RegistrationInvite($invite));

        return $invite;
    }

    public function findByTeam(Team $team): InviteCollection
    {
        return $this->inviteRepository->findByTeam($team);
    }

    public function findById(string $inviteId): Invite
    {
        return $this->inviteRepository->findById($inviteId);
    }

    public function findTeamInvite(Team $team, string $email): Invite
    {
        return $this->inviteRepository->findTeamInvite($team, $email);
    }

    public function delete(Invite $invite): void
    {
        $this->inviteRepository->delete($invite);
    }
}
