<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\DashboardMetricsRepository;
use Illuminate\Support\Facades\Cache;

class DashboardMetricsService
{
    public function __construct(protected DashboardMetricsRepository $dashboardMetricsRepository)
    {
    }

    /**
     * Get all metrics for the dashboard
     */
    public function getMetrics(): array
    {
        return Cache::flexible('dashboard-metrics', [30, 60], fn () => [
            'totalFlags' => $this->dashboardMetricsRepository->getTotalFlags(),
            'changesMetrics' => $this->dashboardMetricsRepository->getChangesMetrics(),
            'createdMetrics' => $this->dashboardMetricsRepository->getCreatedMetrics(),
            'archivedMetrics' => $this->dashboardMetricsRepository->getArchivedMetrics(),
            'flagStatusData' => $this->dashboardMetricsRepository->getFlagStatusData(),
            'flagTypeData' => $this->dashboardMetricsRepository->getFlagTypeData(),
            'ageData' => $this->dashboardMetricsRepository->getAgeData(),
            'usageOverTimeData' => $this->dashboardMetricsRepository->getUsageOverTimeData(),
            'usageData' => $this->dashboardMetricsRepository->getUsageData(),
            'oldestData' => $this->dashboardMetricsRepository->getOldestFlags(),
        ]);
    }


}
