<?php

declare(strict_types=1);

namespace App\Actions\Policies;

use App\Actions\Traits\AsAction;
use App\Models\Policy as PolicyModel;
use App\Values\Policy;

class UpdatePolicy
{
    use AsAction;

    public function handle(Policy $policy): Policy
    {
        $model = PolicyModel::findOrFail($policy->id);
        $model->fill($policy->toArray());
        $model->save();

        return Policy::from($model);
    }
}
