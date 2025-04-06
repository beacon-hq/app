<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Policy;
use App\Values\Collections\PolicyCollection;
use App\Values\Collections\PolicyDefinitionCollection;
use App\Values\Policy as PolicyValue;
use Illuminate\Support\Arr;

class PolicyRepository
{
    public function all(array|string $orderBy = ['name']): PolicyCollection
    {
        $query = Policy::query();

        foreach (Arr::wrap($orderBy) as $column) {
            $query = $query->orderBy($column);
        }

        return PolicyValue::collect($query->get());
    }

    public function create(PolicyValue $policy): PolicyValue
    {
        return PolicyValue::from(Policy::create(
            $policy
            ->with(definition: $policy->definition ?? PolicyDefinitionCollection::empty())
            ->toArray()
        ));
    }

    public function update(PolicyValue $policy): PolicyValue
    {
        Policy::findOrFail($policy->id)->update(
            $policy
                ->with(definition: $policy->definition ?? PolicyDefinitionCollection::empty())
                ->toCollection()
                ->except('id')
                ->toArray()
        );

        return $policy;
    }

    public function find(string $id): PolicyValue
    {
        return PolicyValue::from(Policy::findOrFail($id));
    }
}
