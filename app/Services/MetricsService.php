<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\MetricsRepository;
use Illuminate\Support\Facades\Cache;

class MetricsService
{
    public function __construct(protected MetricsRepository $metricsRepository)
    {
    }

    public function getDashboardMetrics(): array
    {
        return Cache::flexible('dashboard-metrics', [30, 60], fn () => [
            'totalFlags' => $this->metricsRepository->getTotalFlags(),
            'changesMetrics' => $this->metricsRepository->getChangesMetrics(),
            'createdMetrics' => $this->metricsRepository->getCreatedMetrics(),
            'archivedMetrics' => $this->metricsRepository->getArchivedMetrics(),
            'flagStatusData' => $this->metricsRepository->getFlagLifeCycleMetrics(),
            'flagTypeData' => $this->metricsRepository->getFlagTypeData(),
            'ageData' => $this->metricsRepository->getAgeData(),
            'usageOverTimeData' => $this->metricsRepository->getUsageOverTimeData(),
            'usageData' => $this->metricsRepository->getUsageData(),
            'oldestData' => $this->metricsRepository->getOldestFlags(),
        ]);
    }

    public function getPlanMetrics(): array
    {
        return Cache::flexible('plan-metrics', [30, 60], fn () => [
            'evaluations' => $this->metricsRepository->getPlanUsage(),
        ]);
    }

    public function getFlagMetrics(string $featureFlagId, ?string $applicationId = null, ?string $environmentId = null): array
    {
        return $this->metricsRepository->getFlagMetrics($featureFlagId, $applicationId, $environmentId);
    }
}
