<?php

declare(strict_types=1);

namespace App\Actions\Metrics;

use App\Actions\AppContext\GetAppContext;
use App\Actions\Traits\AsAction;
use App\Models\Activity;
use App\Models\FeatureFlag;
use Beacon\Metrics\Metrics;
use Beacon\Metrics\Values\TrendMetric;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use stdClass;

class GetDashboardMetrics
{
    use AsAction;

    public function handle(): array
    {
        return Cache::flexible('dashboard-metrics', [30, 60], function () {
            return [
                'totalFlags' => $this->getTotalFlags(),
                'changesMetrics' => $this->getChangesMetrics(),
                'createdMetrics' => $this->getCreatedMetrics(),
                'completedMetrics' => $this->getCompletedMetrics(),
                'flagStatusData' => $this->getFlagLifeCycleMetrics(),
                'flagTypeData' => $this->getFlagTypeData(),
                'ageData' => $this->getAgeData(),
                'usageOverTimeData' => $this->getUsageOverTimeData(),
                'usageData' => $this->getUsageData(),
                'oldestData' => $this->getOldestFlags(),
            ];
        });
    }

    public function getUnusedFlagMetrics(): TrendMetric
    {
        $query = FeatureFlag::query()
            ->whereNull('completed_at')
            ->whereDoesntHave('usages', function (Builder $query) {
                $query->whereNotNull('feature_flag_id');
            });

        return Metrics::query($query)
            ->countByDay()
            ->dateColumn('created_at')
            ->from(now()->subDays(30))
            ->trends();
    }

    public function getActiveFlagMetrics(): TrendMetric
    {
        $query = FeatureFlag::query()
            ->whereNull('completed_at')
            ->where('status', true)
            ->whereHas('usages')
            ->whereHas('statuses', function (Builder $query) {
                $query->where('status', true);
            })
            ->whereDate('last_seen_at', '>=', now()->subDays(30));

        return Metrics::query($query)
            ->countByDay()
            ->dateColumn('last_seen_at')
            ->from(now()->subDays(30))
            ->trends();
    }

    public function getInactiveFlagMetrics(): TrendMetric
    {
        $query = FeatureFlag::query()
            ->whereNull('completed_at')
            ->whereHas('featureType', function (Builder $query) {
                $query->where('temporary', true);
            })
            ->where(function (Builder $query) {
                $query->where('status', false)
                    ->orWhereDoesntHave('statuses', function (Builder $query) {
                        $query->where('status', true);
                    });
            });

        return Metrics::query($query)
            ->countByDay()
            ->dateColumn('last_seen_at')
            ->all()
            ->trends();
    }

    public function getStaleFlagMetrics(): TrendMetric
    {
        $query = FeatureFlag::query()
            ->whereNull('completed_at')
            ->join('feature_types', 'feature_types.id', '=', 'feature_flags.feature_type_id')
            ->where('temporary', true)
            ->where(function (Builder $query) {
                $query->where('status', true)
                    ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('feature_flag_statuses')
                            ->whereColumn('feature_flag_statuses.feature_flag_id', 'feature_flags.id')
                            ->where('feature_flag_statuses.status', true);
                    });
            })
            ->whereDate('last_seen_at', '<', now()->subDays(30));

        return Metrics::query($query)
            ->countByDay()
            ->dateColumn('last_seen_at')
            ->all()
            ->trends();
    }

    protected function getTotalFlags(): array
    {
        $total = Metrics::query(FeatureFlag::query()->whereNull('completed_at'))
            ->count()
            ->all()
            ->value();
        $difference = Metrics::query(FeatureFlag::query()->whereNull('completed_at'))
            ->between(now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth())
            ->count()
            ->value();

        return [
            'value' => $total->value,
            'previous' => [
                'difference' => $total->value - ($total->value - $difference->value),
                'type' => 'increase',
            ],
        ];
    }

    protected function getChangesMetrics(): array
    {
        $query = Activity::query();

        return Metrics::query($query)
            ->count()
            ->dateColumn('created_at')
            ->between(
                now()->startOfMonth(),
                now()->endOfMonth()
            )
            ->withPrevious()
            ->value()
            ->toArray();

    }

    protected function getCreatedMetrics(): array
    {
        return Metrics::query(FeatureFlag::query()->whereNull('completed_at'))
            ->count('created_at')
            ->between(
                now()->startOfMonth(),
                now()->endOfMonth()
            )
            ->withPrevious()
            ->value()
            ->toArray();
    }

    protected function getCompletedMetrics(): array
    {
        $query = FeatureFlag::whereNotNull('completed_at');

        return Metrics::query($query)
            ->count()
            ->dateColumn('completed_at')
            ->between(
                now()->startOfMonth(),
                now()
            )
            ->byMonth()
            ->withPrevious()
            ->value()
            ->toArray();
    }

    protected function getFlagLifeCycleMetrics(): array
    {
        $unused = $this->getUnusedFlagMetrics();
        $active = $this->getActiveFlagMetrics();
        $stale = $this->getStaleFlagMetrics();
        $inactive = $this->getInactiveFlagMetrics();

        return [
            ['state' => 'unused', 'flags' => $unused->total, 'fill' => 'var(--color-unused)'],
            ['state' => 'active', 'flags' => $active->total, 'fill' => 'var(--color-active)'],
            ['state' => 'stale', 'flags' => $stale->total, 'fill' => 'var(--color-stale)'],
            ['state' => 'inactive', 'flags' => $inactive->total, 'fill' => 'var(--color-inactive)'],
        ];
    }

    protected function getFlagTypeData(): array
    {
        $metrics = FeatureFlag::select([DB::raw('COUNT(feature_flags.id) as flags'), 'feature_types.id', 'feature_types.name as type', 'feature_types.color as fill'])
            ->join('feature_types', 'feature_types.id', '=', 'feature_flags.feature_type_id')
            ->groupBy('feature_types.id', 'feature_types.name', 'feature_types.color')->get();


        return [
            'data' => $metrics->toArray(),
            'total' => $metrics->sum('flags')
        ];
    }

    protected function getAgeData(): array
    {
        $months = $this->getLastSixMonths();

        $result = [];
        foreach ($months as $month) {
            $startDate = CarbonImmutable::parse($month . '-01');
            $endDate = CarbonImmutable::parse($month . '-01')->endOfMonth();

            // Calculate average age in days for flags that existed in this month
            // Using database-agnostic date difference calculation
            $query = FeatureFlag::where(function ($query) use ($startDate) {
                $query->whereNull('created_at')
                    ->orWhere('created_at', '<=', $startDate->endOfMonth());
            });

            $averageAge = $query->selectRaw(
                config('database.default') === 'sqlite'
                    ? 'AVG(julianday(?) - julianday(created_at)) as avg_age'
                    : 'AVG(DATE_PART(\'day\', ?::timestamp - created_at::timestamp)) as avg_age',
                [$endDate->format('Y-m-d')]
            )->first()->avg_age ?? 0;

            $result[] = [
                'month' => $startDate->format('F'),
                'age' => (int) round((int) $averageAge),
            ];
        }

        return $result;
    }

    protected function getUsageOverTimeData(): array
    {
        $result = [];
        $months = $this->getLastSixMonths();

        foreach ($months as $month) {
            $startDate = CarbonImmutable::parse($month . '-01');
            $endDate = CarbonImmutable::parse($month . '-01')->endOfMonth();

            // Use a single query for both active and inactive counts
            $usageCounts = DB::table('feature_flag_usages')
                ->select(
                    'feature_flag_usages.active',
                    DB::raw('COUNT(feature_flag_usages.id) as count')
                )
                ->where('feature_flag_usages.team_id', GetAppContext::team()->id)
                ->whereDate('feature_flag_usages.evaluated_at', '>=', $startDate)
                ->whereDate('feature_flag_usages.evaluated_at', '<=', $endDate)
                ->groupBy('feature_flag_usages.active')
                ->get();

            // Initialize counts
            $activeUsageCount = 0;
            $inactiveUsageCount = 0;

            // Assign counts from query results
            foreach ($usageCounts as $usage) {
                if ($usage->active) {
                    $activeUsageCount = $usage->count;
                } else {
                    $inactiveUsageCount = $usage->count;
                }
            }

            $result[] = [
                'month' => $startDate->format('F'),
                'active' => $activeUsageCount,
                'inactive' => $inactiveUsageCount,
            ];
        }

        return $result;
    }

    protected function getUsageData(): array
    {
        // Get date range for the last six months
        $startDate = now()->subMonths(6)->startOfMonth();
        $endDate = now()->endOfMonth();

        // Query for top flags by usage count over the last six months
        $topFlags = DB::table('feature_flags')
            ->select(
                'feature_flags.id',
                'feature_flags.name',
                'applications.name as application_name',
                'applications.color as application_color',
                'feature_types.color as type_color',
                'feature_types.icon as type_icon',
                DB::raw('COUNT(feature_flag_usages.id) as usages')
            )
            ->join('feature_flag_usages', 'feature_flags.id', '=', 'feature_flag_usages.feature_flag_id')
            ->join('applications', 'applications.id', '=', 'feature_flag_usages.application_id')
            ->join('feature_types', 'feature_types.id', '=', 'feature_flags.feature_type_id')
            ->where('feature_flags.team_id', GetAppContext::team()->id)
            ->whereDate('feature_flag_usages.evaluated_at', '>=', $startDate)
            ->whereDate('feature_flag_usages.evaluated_at', '<=', $endDate)
            ->groupBy('feature_flags.id', 'feature_flags.name', 'applications.name', 'applications.color', 'feature_types.id', 'feature_types.color')
            ->orderByDesc('usages')
            ->limit(6)
            ->get();

        // If no results, return empty array
        if ($topFlags->isEmpty()) {
            return [];
        }

        // Format the results
        return $topFlags->map(function (stdClass $flag) {
            return [
                'name' => $flag->name,
                'application' => [
                    'name' => $flag->application_name,
                    'color' => $flag->application_color,
                ],
                'feature_type' => [
                    'color' => $flag->type_color,
                    'icon' => $flag->type_icon,
                ],
                'usages' => (int) $flag->usages,
            ];
        })->toArray();
    }

    protected function getOldestFlags(): array
    {
        $oldestFlags = DB::table('feature_flags')
            ->select(
                'feature_flags.id',
                'feature_flags.name',
                'applications.name as application_name',
                'applications.color as application_color',
                'feature_types.color as type_color',
                'feature_types.icon as type_icon',
                'feature_flag_statuses.created_at',
            )
            ->join('feature_flag_statuses', 'feature_flags.id', '=', 'feature_flag_statuses.feature_flag_id')
            ->join('applications', 'applications.id', '=', 'feature_flag_statuses.application_id')
            ->join('feature_types', 'feature_types.id', '=', 'feature_flags.feature_type_id')
            ->where('feature_types.temporary', true)
            ->where('feature_flags.team_id', GetAppContext::team()->id)
            ->orderByDesc('feature_flag_statuses.created_at')
            ->limit(6)
            ->get();


        return $oldestFlags->map(function (stdClass $flag) {
            return [
                'name' => $flag->name,
                'application' => [
                    'name' => $flag->application_name,
                    'color' => $flag->application_color,
                ],
                'feature_type' => [
                    'color' => $flag->type_color,
                    'icon' => $flag->type_icon,
                ],
                'created_at' => $flag->created_at,
            ];
        })->toArray();
    }

    protected function getLastSixMonths(): array
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $months[] = now()->subMonths($i)->format('Y-m');
        }

        return $months;
    }
}
