<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\FeatureType;
use App\Values\FeatureType as FeatureTypeValue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class FeatureTypeRepository
{
    public function all(array|string $orderBy = ['name']): Collection
    {
        $query = FeatureType::query();

        foreach (Arr::wrap($orderBy) as $column) {
            $query = $query->orderBy($column);
        }

        return $query->get();
    }

    public function create(FeatureTypeValue $featureType): FeatureType
    {
        return FeatureType::create(
            $featureType
                ->with(color: $featureType->color ?? '')
                ->toArray()
        );
    }

    public function update(FeatureTypeValue $featureType): FeatureType
    {
        $featureTypeModel = FeatureType::findOrFail($featureType->id);

        $featureTypeModel->update(
            $featureType
                ->with(color: $featureType->color ?? '')
                ->toCollection()
                ->except('name', 'id')
                ->toArray()
        );

        return $featureTypeModel->fresh();
    }

    public function find(?string $id): FeatureType
    {
        return FeatureType::findOrFail($id);
    }
}
