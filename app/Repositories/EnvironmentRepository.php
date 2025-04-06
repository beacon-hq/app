<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Environment;
use App\Values\Collections\EnvironmentCollection;
use App\Values\Environment as EnvironmentValue;
use Illuminate\Support\Arr;

class EnvironmentRepository
{
    public function all(array|string $orderBy = ['name']): EnvironmentCollection
    {
        $query = Environment::query();

        foreach (Arr::wrap($orderBy) as $column) {
            $query = $query->orderBy($column);
        }

        return EnvironmentValue::collect($query->get());
    }

    public function create(EnvironmentValue $environment): EnvironmentValue
    {
        return EnvironmentValue::from(Environment::create(
            $environment
                ->with(color: $environment->color ?? '')
                ->toArray()
        ));
    }

    public function update(EnvironmentValue $environment): EnvironmentValue
    {
        Environment::find($environment->id)->update(
            $environment
                ->with(color: $environment->color ?? '')
                ->toCollection()
                ->except('name', 'id')
                ->toArray()
        );

        return $environment;
    }

    public function find(string $id)
    {
        return EnvironmentValue::from(Environment::findOrFail($id));
    }
}
