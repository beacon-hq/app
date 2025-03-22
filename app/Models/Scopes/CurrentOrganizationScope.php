<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CurrentOrganizationScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $currentOrganization = App::context()->organization;

        if ($currentOrganization === null) {
            return;
        }

        if (method_exists($model, 'organizations')) {
            $builder->whereHas('organizations', function (Builder $query) use ($currentOrganization) {
                $query->where('id', $currentOrganization->id);
            });

            return;
        }

        $builder->where($builder->qualifyColumn('organization_id'), $currentOrganization->id);
    }
}
