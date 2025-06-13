<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\FeatureFlag;
use Beacon\Metrics\Metrics;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

class DashboardMetricsRepository
{
    /**
     * Get total number of feature flags
     */
    public function getTotalFlags(): array
    {
        return [
            'value' => Metrics::query(FeatureFlag::query())->count()->all()->value(),
            'previous' => [
                'value' => Metrics::query(FeatureFlag::query())
                    ->count()
                    ->byMonth()
                    ->between(now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth())
                    ->value(),
                'type' => 'increase',
            ],
        ];
    }

    /**
     * Get metrics for changes to feature flags
     */
    public function getChangesMetrics(): array
    {
        $query = FeatureFlag::query()->where('updated_at', '>', DB::raw('created_at'));

        return Metrics::query($query)
            ->count()
            ->dateColumn('updated_at')
            ->between(
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth()
            )
            ->withPrevious()
            ->value()
            ->toArray();
    }

    /**
     * Get metrics for created feature flags
     */
    public function getCreatedMetrics(): array
    {
        return Metrics::query(FeatureFlag::query())
            ->count()
            ->between(
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth()
            )
            ->withPrevious()
            ->value()
            ->toArray();
    }

    /**
     * Get metrics for archived feature flags
     */
    public function getArchivedMetrics(): array
    {
        $query = FeatureFlag::where('status', false);

        return Metrics::query($query)
            ->count()
            ->dateColumn('updated_at')
            ->between(
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth()
            )
            ->byMonth()
            ->withPrevious()
            ->value()->toArray();
    }

    /**
     * Get flag status data (active, stale, inactive)
     */
    public function getFlagStatusData(): array
    {
        // Active flags (last seen in the last 30 days)
        $activeFlags = FeatureFlag::query()
            ->where('status', true)
            ->whereDate('last_seen_at', '>=', now()->subDays(30))
            ->count();

        // Stale flags (last seen more than 30 days ago)
        $staleFlags = FeatureFlag::query()
            ->where('status', true)
            ->where(function (Builder $query) {
                $query->whereNotNull('last_seen_at')
                    ->whereDate('last_seen_at', '<', now()->subDays(30));
            })
            ->orWhere(function (Builder $query) {
                $query->whereNull('last_seen_at')
                    ->where('created_at', '>=', now()->subDays(30));
            })
            ->count();

        // Inactive flags (never seen)
        $inactiveFlags = FeatureFlag::query()
            ->where('status', false)
            ->count();

        return [
            ['state' => 'active', 'flags' => $activeFlags, 'fill' => 'var(--color-active)'],
            ['state' => 'stale', 'flags' => $staleFlags, 'fill' => 'var(--color-stale)'],
            ['state' => 'inactive', 'flags' => $inactiveFlags, 'fill' => 'var(--color-inactive)'],
        ];
    }

    /**
     * Get flag type data (release, kill, experiment)
     */
    public function getFlagTypeData(): array
    {
        $query = FeatureFlag::select([DB::raw('COUNT(feature_flags.id) as flags'), 'feature_types.id', 'feature_types.name as type', 'feature_types.color as fill'])
            ->join('feature_types', 'feature_types.id', '=', 'feature_flags.feature_type_id')
            ->groupBy('feature_types.id', 'feature_types.name', 'feature_types.color');

        return $query
            ->get()->toArray();
    }

    /**
     * Get average age data by month
     */
    public function getAgeData(): array
    {
        $months = $this->getLastSixMonths();

        $result = [];
        foreach ($months as $month) {
            $startDate = Carbon::parse($month . '-01');
            $endDate = Carbon::parse($month . '-01')->endOfMonth();

            // Calculate average age in days for flags that existed in this month
            // Using database-agnostic date difference calculation
            $query = FeatureFlag::where(function ($query) use ($startDate) {
                $query->whereNull('created_at')
                    ->orWhere('created_at', '<=', $startDate->endOfMonth());
            });

            $averageAge = $query->selectRaw('AVG(DATE_PART(\'day\', ?::timestamp - created_at::timestamp)) as avg_age', [$endDate->format('Y-m-d')])
                ->first()
                ->avg_age ?? 0;

            $result[] = [
                'month' => $startDate->format('F'),
                'age' => (int) round((int) $averageAge),
            ];
        }

        return $result;
    }

    /**
     * Get usage over time data
     */
    public function getUsageOverTimeData(): array
    {
        $result = [];
        $months = $this->getLastSixMonths();

        foreach ($months as $month) {
            $startDate = Carbon::parse($month . '-01');
            $endDate = Carbon::parse($month . '-01')->endOfMonth();

            // Use a single query for both active and inactive counts
            $usageCounts = DB::table('feature_flag_usages')
                ->select(
                    'feature_flag_usages.active',
                    DB::raw('COUNT(feature_flag_usages.id) as count')
                )
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

    /**
     * Get usage data for top flags
     */
    public function getUsageData(): array
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

    /**
     * Get oldest flags data
     */
    public function getOldestFlags(): array
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

    /**
     * Helper to get the last six months in Y-m format
     */
    protected function getLastSixMonths(): array
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $months[] = now()->subMonths($i)->format('Y-m');
        }

        return $months;
    }
}
