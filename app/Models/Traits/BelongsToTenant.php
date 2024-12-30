<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\Scopes\CurrentTenantScope;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\App;

/**
 * @mixin Model
 */
trait BelongsToTenant
{
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    protected static function bootBelongsToTenant()
    {
        static::addGlobalScope(CurrentTenantScope::class);

        static::creating(function (Model $model) {
            if (App::context()->tenant !== null) {
                if (!\method_exists($model, 'tenants')) {
                    if ($model->tenant_id === null) {
                        $model->tenant_id = App::context()->tenant->id;
                    }
                }
            }
        });

        static::created(function (Model $model) {
            if (App::context()->tenant !== null) {
                if (\method_exists($model, 'tenants')) {
                    $model->tenants()->attach(App::context()->tenant->id);
                }
            }
        });
    }
}
