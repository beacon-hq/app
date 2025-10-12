<?php

declare(strict_types=1);

namespace App\Actions\Organizations;

use App\Actions\Traits\AsAction;
use App\Models\Organization as OrganizationModel;
use App\Models\Scopes\CurrentOrganizationScope;
use App\Values\Collections\OrganizationCollection;
use App\Values\Organization;
use App\Values\User;
use Illuminate\Database\Eloquent\Builder;

class ListOrganizations
{
    use AsAction;

    public function handle(User $user): OrganizationCollection
    {
        $query = OrganizationModel::whereHas(
            'users',
            fn (Builder $query) => $query
                ->withoutGlobalScopes([CurrentOrganizationScope::class])
                ->where('id', $user->id)
        );

        $organizations = $query->get();

        return Organization::collect($organizations);
    }
}
