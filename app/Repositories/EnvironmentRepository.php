<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Environment;
use App\Values\Environment as EnvironmentValue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class EnvironmentRepository
{
    public function all(array|string $orderBy = ['name']): Collection
    {
        $query = Environment::query();

        foreach (Arr::wrap($orderBy) as $column) {
            $query = $query->orderBy($column);
        }

        return $query->get();
    }

    public function create(EnvironmentValue $environment): Environment
    {
        return Environment::create(
            $environment
                ->with(color: $environment->color ?? '')
                ->toArray()
        );
    }

    public function update(EnvironmentValue $environment): Environment
    {
        $env = Environment::findOrFail($environment->id);

        $env->update(
            $environment
                ->with(color: $environment->color ?? '')
                ->toCollection()
                ->except('name', 'id')
                ->toArray()
        );

        return $env->fresh();
    }

    public function find(string $id): Environment
    {
        return Environment::findOrFail($id);
    }
}
