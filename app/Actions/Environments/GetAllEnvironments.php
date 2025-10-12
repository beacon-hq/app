<?php

declare(strict_types=1);

namespace App\Actions\Environments;

use App\Actions\Traits\AsAction;
use App\Models\Environment as EnvironmentModel;
use App\Values\Collections\EnvironmentCollection;
use App\Values\Environment;
use Illuminate\Support\Arr;

class GetAllEnvironments
{
    use AsAction;

    public function handle(array|string $orderBy = ['name']): EnvironmentCollection
    {
        $query = EnvironmentModel::query();

        foreach (Arr::wrap($orderBy) as $column) {
            $query = $query->orderBy($column);
        }

        $environments = $query->get();

        return Environment::collect($environments);
    }
}
