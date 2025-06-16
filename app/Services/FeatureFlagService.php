<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\FeatureFlagRepository;
use App\Values\ActivityLog;
use App\Values\Collections\ActivityLogCollection;
use App\Values\Collections\FeatureFlagCollection;
use App\Values\FeatureFlag;

class FeatureFlagService
{
    public function __construct(protected FeatureFlagRepository $featureFlagRepository)
    {
    }

    public function all(?int $page = null, int $perPage = 20, array $filters = [], array $sort = []): FeatureFlagCollection
    {
        $orderBy = ['name']; // Default ordering

        if (!empty($sort)) {
            $orderBy = [];
            foreach ($sort as $column => $direction) {
                $orderBy[] = $direction === 'desc' ? "-{$column}" : $column;
            }
        }

        $featureFlags = $this->featureFlagRepository->all(orderBy: $orderBy, page: $page, perPage: $perPage, filters: $filters);

        return FeatureFlag::collect($featureFlags);
    }

    public function create(FeatureFlag $featureFlag): FeatureFlag
    {
        $createdFeatureFlag = $this->featureFlagRepository->create($featureFlag);

        return FeatureFlag::from($createdFeatureFlag);
    }

    public function update(FeatureFlag $featureFlag): FeatureFlag
    {
        $updatedFeatureFlag = $this->featureFlagRepository->update($featureFlag);

        return FeatureFlag::from($updatedFeatureFlag);
    }

    public function count(array $filters = []): int
    {
        return $this->featureFlagRepository->count($filters);
    }

    public function find(string $id): FeatureFlag
    {
        $featureFlag = $this->featureFlagRepository->find($id);

        return FeatureFlag::from($featureFlag);
    }

    public function findByName(string $name): FeatureFlag
    {
        $featureFlag = $this->featureFlagRepository->findByName($name);

        return FeatureFlag::from($featureFlag);
    }

    public function touch(FeatureFlag $featureFlag): FeatureFlag
    {
        $updatedFeatureFlag = $this->featureFlagRepository->update($featureFlag->with(lastSeenAt: now()));

        return FeatureFlag::from($updatedFeatureFlag);
    }

    public function activityLog(string $id): ActivityLogCollection
    {
        $activityLogs = $this->featureFlagRepository->activityLog($id);

        return ActivityLog::collect($activityLogs);
    }
}
