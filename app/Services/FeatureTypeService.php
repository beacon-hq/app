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
        return $this->featureTypeRepository->all();
    }

    public function create(FeatureType $featureType): FeatureType
    {
        return $this->featureTypeRepository->create($featureType);
    }

    public function update(FeatureType $featureType): FeatureType
    {
        return $this->featureTypeRepository->update($featureType);
    }

    public function findBySlug(?string $slug): FeatureType
    {
        return $this->featureTypeRepository->findBySlug($slug);
    }
}
