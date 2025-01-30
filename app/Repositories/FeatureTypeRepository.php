<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\FeatureType;
use App\Values\Collections\FeatureTypeCollection;
use App\Values\FeatureType as FeatureTypeValue;
use Illuminate\Support\Arr;

class FeatureTypeRepository
{
    public function all(array|string $orderBy = ['name']): FeatureTypeCollection
    {
        $query = FeatureType::query();

        foreach (Arr::wrap($orderBy) as $column) {
            $query = $query->orderBy($column);
        }

        return FeatureTypeValue::collect($query->get());
    }

    public function create(FeatureTypeValue $featureType): FeatureTypeValue
    {
        return FeatureTypeValue::from(FeatureType::create(
            $featureType
                ->with(color: $featureType->color ?? '')
                ->toArray()
        ));
    }

    public function update(FeatureTypeValue $featureType): FeatureTypeValue
    {
        FeatureType::where('slug', $featureType->slug)->firstOrFail()->update(
            $featureType
                ->with(color: $featureType->color ?? '')
                ->toCollection()
                ->except('name', 'slug')
                ->toArray()
        );

        return $featureType;
    }

    public function findBySlug(?string $slug): FeatureTypeValue
    {
        return FeatureTypeValue::from(FeatureType::where('slug', $slug)->firstOrFail());
    }
}
