<?php

declare(strict_types=1);

namespace App\Actions\Policies;

use App\Actions\Traits\AsAction;
use App\Models\Policy as PolicyModel;
use App\Values\Collections\PolicyDefinitionCollection;
use App\Values\Policy;

class CreatePolicy
{
    use AsAction;

    public function handle(Policy $policy): Policy
    {
        $model = PolicyModel::create($policy->with(definition: PolicyDefinitionCollection::empty())->toArray());

        return Policy::from($model);
    }
}
