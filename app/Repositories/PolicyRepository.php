<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Policy;
use App\Values\Collections\PolicyDefinitionCollection;
use App\Values\Policy as PolicyValue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class PolicyRepository
{
    public function all(array|string $orderBy = ['name']): Collection
    {
        $query = Policy::query();

        foreach (Arr::wrap($orderBy) as $column) {
            $query = $query->orderBy($column);
        }

        return $query->get();
    }

    public function create(PolicyValue $policy): Policy
    {
        return Policy::create(
            $policy
            ->with(definition: $policy->definition ?? PolicyDefinitionCollection::empty())
            ->toArray()
        );
    }

    public function update(PolicyValue $policy): Policy
    {
        $policyModel = Policy::findOrFail($policy->id);

        $policyModel->update(
            $policy
                ->with(definition: $policy->definition ?? PolicyDefinitionCollection::empty())
                ->toArray()
        );

        return $policyModel->fresh();
    }

    public function find(string $id): Policy
    {
        return Policy::findOrFail($id);
    }
}
