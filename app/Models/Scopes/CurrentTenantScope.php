<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CurrentTenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $currentTenant = App::context()->tenant;

        if ($currentTenant === null) {
            return;
        }

        if (method_exists($model, 'tenants')) {
            $builder->whereHas('tenants', function (Builder $query) use ($currentTenant) {
                $query->where('id', $currentTenant->id);
            });

            return;
        }

        $builder->where($builder->qualifyColumn('tenant_id'), $currentTenant->id);
    }
}
