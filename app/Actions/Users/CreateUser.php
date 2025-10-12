<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Actions\AppContext\SetOrganizationContext;
use App\Actions\Invites\DeleteInvite;
use App\Actions\Organizations\CreateOrganization;
use App\Actions\Traits\AsAction;
use App\Enums\Color;
use App\Enums\Role;
use App\Models\User;
use App\Values\Invite;
use App\Values\Organization;
use App\Values\Team;
use App\Values\User as UserValue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CreateUser
{
    use AsAction;

    public function handle(UserValue $user, ?Invite $invite = null): UserValue
    {
        $userModel = User::create($user->toArray());
        $user = UserValue::from($userModel);

        Auth::login($userModel);

        if ($invite !== null) {
            SetOrganizationContext::run($invite->organization);

            $user = AddUserToOrganization::run($user, $invite->organization);
            $user = AddUserToTeam::run($user, $invite->team);
            $user = AssignRoleToUser::run($user, $invite->role);

            DeleteInvite::run($invite);

            Session::forget('invite');

            return $user;
        }

        $organization = CreateOrganization::run($user, Organization::from(name: $user->firstName . '\'s Organization'));

        $user = AddUserToOrganization::run($user, $organization);

        SetOrganizationContext::run($organization);

        $team = Team::from(name: $user->firstName . '\'s Team', color: collect(Color::values())->random());
        $user = AddUserToTeam::run($user, $team);

        $user = AssignRoleToUser::run($user, Role::OWNER);

        return $user;
    }
}
