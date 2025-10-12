<?php

declare(strict_types=1);

namespace App\Actions\Metrics;

use App\Actions\Traits\AsAction;
use App\Models\FeatureFlagUsage;
use Beacon\Metrics\Metrics;
use Beacon\Metrics\Values\Projections;
use Illuminate\Support\Facades\Cache;

/**
 * @method static array run()
 */
class GetPlanMetrics
{
    use AsAction;

    public function handle(): array
    {
        return Cache::flexible('plan-metrics', [30, 60], function () {
            return [
                'evaluations' => $this->getPlanUsage(),
            ];
        });
    }

    protected function getPlanUsage(): array
    {
        $currentMonth = (int) Metrics::query(FeatureFlagUsage::query())
            ->countBetween(now()->startOfMonth(), now())
            ->dateColumn('evaluated_at')
            ->value()->value;

        $previousMonth = (int) Metrics::query(FeatureFlagUsage::query())
            ->countBetween(now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth())
            ->dateColumn('evaluated_at')
            ->value()->value;

        $trend = Metrics::query(FeatureFlagUsage::query())
            ->countByDay()
            ->dateColumn('evaluated_at')
            ->between(now()->startOfMonth(), now())
            ->projectForDate(now()->endOfMonth())
            ->fillMissing()
            ->trends();

        /** @var Projections $projections */
        $projections = $trend->projections;

        return [
            'currentMonth' => $currentMonth,
            'previousMonth' => $previousMonth,
            'projected' => (int) $projections->date->projectedTotal,
            'dailyData' => $trend->assoc(),
        ];
    }
}
