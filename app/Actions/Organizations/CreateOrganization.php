<?php

declare(strict_types=1);

namespace App\Actions\Organizations;

use App\Actions\Traits\AsAction;
use App\Models\Organization as OrganizationModel;
use App\Models\Scopes\CurrentOrganizationScope;
use App\Models\User as UserModel;
use App\Values\Organization;
use App\Values\User;
use Illuminate\Support\Facades\DB;

class CreateOrganization
{
    use AsAction;

    public function handle(User $user, Organization $organization): Organization
    {
        $organizationModel = DB::transaction(function () use ($organization, $user) {
            $organizationModel = OrganizationModel::create([
                'owner_id' => $user->id,
                'name' => $organization->name,
            ]);

            UserModel::query()
                ->withoutGlobalScopes([CurrentOrganizationScope::class])
                ->findOrFail($user->id)
                ->organizations()
                ->attach($organizationModel);

            return $organizationModel;
        });

        return Organization::from(
            id: $organizationModel->id,
            owner: $organizationModel->owner !== null ? User::from($organizationModel->owner) : null,
            name: $organizationModel->name
        );
    }
}
