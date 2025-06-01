<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Role;
use App\Notifications\RegistrationInvite;
use App\Repositories\InviteRepository;
use App\Values\Collections\InviteCollection;
use App\Values\Invite;
use App\Values\Organization;
use App\Values\Team;
use App\Values\User;
use Illuminate\Support\Facades\Notification;

class InviteService
{
    public function __construct(protected InviteRepository $inviteRepository)
    {
    }

    public function create(User $user, Team $team, Organization $organization, string $email, Role $role): Invite
    {
        $inviteModel = $this->inviteRepository->create($user, $team, $organization, $role, $email);
        $invite = Invite::from($inviteModel);
        Notification::send($invite, new RegistrationInvite($invite));

        return $invite;
    }

    public function findByTeam(Team $team): InviteCollection
    {
        $invites = $this->inviteRepository->findByTeam($team);

        return Invite::collect($invites);
    }

    public function findById(string $inviteId): Invite
    {
        $invite = $this->inviteRepository->findById($inviteId);

        return Invite::from($invite);
    }

    public function findTeamInvite(Team $team, string $email): Invite
    {
        $invite = $this->inviteRepository->findTeamInvite($team, $email);

        return Invite::from($invite);
    }

    public function delete(Invite $invite): void
    {
        $this->inviteRepository->delete($invite);
    }

    public function resend(Invite $invite): void
    {
        // Refreshing the invitation expiration time
        $inviteModel = $this->inviteRepository->refreshExpiration($invite);
        $invite = Invite::from($inviteModel);

        // Resend the invitation email
        Notification::send($invite, new RegistrationInvite($invite));
    }

    public function all(): InviteCollection
    {
        $invites = $this->inviteRepository->all();

        return Invite::collect($invites);
    }
}
