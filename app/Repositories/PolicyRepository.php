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
        Policy::where('slug', $policy->slug)->firstOrFail()->update(
            $policy
                ->with(definition: $policy->definition ?? PolicyDefinitionCollection::empty())
                ->toCollection()
                ->except('slug')
                ->toArray()
        );

        return $policy;
    }

    public function findBySlug(string $slug): PolicyValue
    {
        return PolicyValue::from(Policy::where('slug', $slug)->firstOrFail());
    }

    public function findById(string $id): PolicyValue
    {
        return PolicyValue::from(Policy::find($id)->firstOrFail());
    }
}
