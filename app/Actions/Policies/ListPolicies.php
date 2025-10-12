<?php

declare(strict_types=1);

namespace App\Actions\Policies;

use App\Actions\Traits\AsAction;
use App\Models\Policy as PolicyModel;
use App\Values\Collections\PolicyCollection;
use App\Values\Policy;

class ListPolicies
{
    use AsAction;

    public function handle(array|string $orderBy = ['name']): PolicyCollection
    {
        $query = PolicyModel::query();

        if (is_string($orderBy)) {
            $orderBy = [$orderBy];
        }

        foreach ($orderBy as $field) {
            $direction = 'asc';
            if (str_starts_with($field, '-')) {
                $direction = 'desc';
                $field = substr($field, 1);
            }
            $query->orderBy($field, $direction);
        }

        $policies = $query->get();

        return Policy::collect($policies);
    }
}
