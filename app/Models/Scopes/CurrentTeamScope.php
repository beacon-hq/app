<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Scope;

class CurrentTeamScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (!App::context()->has('team')) {
            return;
        }

        $currentTeam = App::context()->team;
        if (method_exists($model, 'teams')) {
            $teams = $model->teams();
            $builder
                ->when(
                    $teams instanceof MorphTo,
                    function (Builder $builder) use ($teams, $currentTeam) {
                        return $builder
                            ->where($teams->getMorphType(), App\Models\Team::class)
                            ->where($teams->getForeignKeyName(), $currentTeam->id);
                    }
                )
                ->when(
                    $teams instanceof HasMany,
                    function (Builder $builder) use ($currentTeam) {
                        return $builder
                            ->whereHas('teams', fn (Builder $query) => $query->where('id', $currentTeam->id));
                    }
                );

            return;
        }


        $builder->where($builder->qualifyColumn('team_id'), $currentTeam->id);
    }
}
