<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\FeatureFlagRepository;
use App\Values\Collections\FeatureFlagCollection;
use App\Values\FeatureFlag;

class FeatureFlagService
{
    public function __construct(protected FeatureFlagRepository $featureFlagRepository)
    {
    }

    public function all(?int $page = null, int $perPage = 20, array $filters = []): FeatureFlagCollection
    {
        return $this->featureFlagRepository->all(page: $page, perPage: $perPage, filters: $filters);
    }

    public function create(FeatureFlag $featureFlag): FeatureFlag
    {
        return $this->featureFlagRepository->create($featureFlag);
    }

    public function findBySlug(string $slug): FeatureFlag
    {
        return $this->featureFlagRepository->findBySlug($slug);
    }

    public function update(FeatureFlag $featureFlag): FeatureFlag
    {
        return $this->featureFlagRepository->update($featureFlag);
    }

    public function count(array $filters = []): int
    {
        return $this->featureFlagRepository->count($filters);
    }
}
