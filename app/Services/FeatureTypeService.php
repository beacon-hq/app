<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\FeatureTypeRepository;
use App\Values\Collections\FeatureTypeCollection;
use App\Values\FeatureType;

class FeatureTypeService
{
    public function __construct(protected FeatureTypeRepository $featureTypeRepository)
    {
    }

    public function all(): FeatureTypeCollection
    {
        $featureTypes = $this->featureTypeRepository->all();

        return FeatureType::collect($featureTypes);
    }

    public function create(FeatureType $featureType): FeatureType
    {
        $createdFeatureType = $this->featureTypeRepository->create($featureType);

        return FeatureType::from($createdFeatureType);
    }

    public function update(FeatureType $featureType): FeatureType
    {
        $updatedFeatureType = $this->featureTypeRepository->update($featureType);

        return FeatureType::from($updatedFeatureType);
    }

    public function find(?string $id): FeatureType
    {
        $featureType = $this->featureTypeRepository->find($id);

        return FeatureType::from($featureType);
    }
}
