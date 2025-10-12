<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\Scopes\CurrentTeamScope;
use App\Models\Team;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Context;

/**
 * @mixin Model
 * @phpstan-require-extends Model
 */
trait BelongsToTeam
{
    /**
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    protected static function bootBelongsToTeam()
    {
        static::addGlobalScope(CurrentTeamScope::class);

        static::creating(
            function (Model $model) {
                if (Context::has('team') && !\method_exists($model, 'teams')) {
                    /** @phpstan-ignore-next-line property.notFound */
                    if ($model->team_id === null) {
                        $model->team_id = Context::get('team')->id;
                    }
                }
            }
        );

        static::created(function (Model $model) {
            if (Context::has('team') && \method_exists($model, 'teams')) {
                $model->teams()->attach(Context::get('team')->id);
            }
        });
    }
}
