<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\Organization;
use App\Models\Scopes\CurrentOrganizationScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\App;

/**
 * @mixin Model
 * @phpstan-require-extends Model
 */
trait BelongsToOrganization
{
    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    protected static function bootBelongsToOrganization()
    {
        static::addGlobalScope(CurrentOrganizationScope::class);

        static::creating(
            function (Model $model) {
                if (App::context()->has('organization') && !\method_exists($model, 'organizations')) {
                    /** @phpstan-ignore-next-line property.notFound */
                    if ($model->organization_id === null) {
                        $model->organization_id = App::context()->organization->id;
                    }
                }
            }
        );

        static::created(function (Model $model) {
            if (App::context()->has('organization') && \method_exists($model, 'organizations')) {
                $model->teams()->attach(App::context()->organization->id);
            }
        });
    }
}
