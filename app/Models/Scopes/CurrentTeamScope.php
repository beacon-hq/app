<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CurrentTeamScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $currentTeam = App::context()->team;

        if ($currentTeam === null) {
            return;
        }

        if (method_exists($model, 'teams')) {
            $builder->whereHas('teams', function (Builder $query) use ($currentTeam) {
                $query->where('id', $currentTeam->id);
            });

            return;
        }

        $builder->where($builder->qualifyColumn('team_id'), $currentTeam->id);
    }
}
