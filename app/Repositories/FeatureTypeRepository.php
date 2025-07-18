<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\FeatureType;
use App\Values\FeatureType as FeatureTypeValue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

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
        return DB::transaction(function () use ($featureType) {
            if ($featureType->isDefault) {
                $this->clearExistingDefault();
            }

            return FeatureType::create(
                $featureType
                    ->with(color: $featureType->color ?? '')
                    ->toArray()
            );
        });
    }

    public function update(FeatureTypeValue $featureType): FeatureType
    {
        return DB::transaction(function () use ($featureType) {
            $featureTypeModel = FeatureType::findOrFail($featureType->id);

            if ($featureType->isDefault && !$featureTypeModel->is_default) {
                $this->clearExistingDefault();
            }

            $featureTypeModel->update(
                $featureType
                    ->with(color: $featureType->color ?? '')
                    ->toCollection()
                    ->except('name', 'id')
                    ->toArray()
            );

            return $featureTypeModel->fresh();
        });
    }

    public function find(?string $id): FeatureType
    {
        return FeatureType::findOrFail($id);
    }

    public function findByName(string $string): FeatureType
    {
        return FeatureType::where('name', $string)->firstOrFail();
    }

    public function getDefault(): FeatureType
    {
        return FeatureType::where('is_default', true)->firstOrFail();
    }

    public function setAsDefault(string $id): FeatureType
    {
        return DB::transaction(function () use ($id) {
            $this->clearExistingDefault();

            $featureType = FeatureType::findOrFail($id);
            $featureType->update(['is_default' => true]);

            return $featureType;
        });
    }

    private function clearExistingDefault(): void
    {
        FeatureType::where('is_default', true)->update(['is_default' => false]);
    }
}
