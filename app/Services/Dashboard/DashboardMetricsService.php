<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\FeatureFlag;
use App\Models\FeatureType;
use App\Values\Team;
use Carbon\Carbon;
use Eliseekn\LaravelMetrics\LaravelMetrics;
use Illuminate\Support\Facades\DB;

class DashboardMetricsService
{
    /**
     * Get all metrics for the dashboard
     */
    public function getMetrics(Team $team): array
    {
        return [
            'totalFlags' => $this->getTotalFlags($team),
            'changesMetrics' => $this->getChangesMetrics($team),
            'createdMetrics' => $this->getCreatedMetrics($team),
            'archivedMetrics' => $this->getArchivedMetrics($team),
            'flagStatusData' => $this->getFlagStatusData($team),
            'flagTypeData' => $this->getFlagTypeData($team),
            'ageData' => $this->getAgeData($team),
            'usageOverTimeData' => $this->getUsageOverTimeData($team),
            'usageData' => $this->getUsageData($team),
            'oldestData' => $this->getOldestFlags($team),
        ];
    }

    /**
     * Get total number of feature flags
     */
    private function getTotalFlags(Team $team): int
    {
        return LaravelMetrics::query(FeatureFlag::where('team_id', $team->id))
            ->count()
            ->metrics();
    }

    /**
     * Get metrics for changes to feature flags
     */
    private function getChangesMetrics(Team $team): array
    {
        // Get total changes metrics
        $totalChanges = FeatureFlag::where('team_id', $team->id)
            ->whereColumn('updated_at', '>', 'created_at')
            ->count();

        // Get changes for this month
        $changesThisMonth = LaravelMetrics::query(
            FeatureFlag::where('team_id', $team->id)
                ->whereColumn('updated_at', '>', 'created_at')
        )
            ->count()
            ->dateColumn('updated_at')
            ->byMonth(1)
            ->metrics();

        // Get changes for last month
        $changesLastMonth = LaravelMetrics::query(
            FeatureFlag::where('team_id', $team->id)
                ->whereColumn('updated_at', '>', 'created_at')
        )
            ->count()
            ->dateColumn('updated_at')
            ->byMonth(1)
            ->forMonth(now()->subMonth()->month)
            ->forYear(now()->subMonth()->year)
            ->metrics();

        return [
            'total' => $totalChanges,
            'thisMonth' => $changesThisMonth,
            'change' => $changesThisMonth - $changesLastMonth,
        ];
    }

    /**
     * Get metrics for created feature flags
     */
    private function getCreatedMetrics(Team $team): array
    {
        // Get created for this month
        $createdThisMonth = LaravelMetrics::query(
            FeatureFlag::where('team_id', $team->id)
        )
            ->count()
            ->byMonth(1)
            ->metrics();

        // Get created for last month
        $createdLastMonth = LaravelMetrics::query(
            FeatureFlag::where('team_id', $team->id)
        )
            ->count()
            ->byMonth(1)
            ->forMonth(now()->subMonth()->month)
            ->forYear(now()->subMonth()->year)
            ->metrics();

        return [
            'total' => $createdThisMonth,
            'change' => $createdThisMonth - $createdLastMonth,
        ];
    }

    /**
     * Get metrics for archived feature flags
     */
    private function getArchivedMetrics(Team $team): array
    {
        // Get total archived
        $archivedTotal = FeatureFlag::where('team_id', $team->id)
            ->where('status', false)
            ->count();

        // Get archived for this month
        $archivedThisMonth = LaravelMetrics::query(
            FeatureFlag::where('team_id', $team->id)
                ->where('status', false)
        )
            ->count()
            ->dateColumn('updated_at')
            ->byMonth(1)
            ->metrics();

        // Get archived for last month
        $archivedLastMonth = LaravelMetrics::query(
            FeatureFlag::where('team_id', $team->id)
                ->where('status', false)
        )
            ->count()
            ->dateColumn('updated_at')
            ->byMonth(1)
            ->forMonth(now()->subMonth()->month)
            ->forYear(now()->subMonth()->year)
            ->metrics();

        return [
            'total' => $archivedTotal,
            'change' => $archivedThisMonth - $archivedLastMonth,
        ];
    }

    /**
     * Get flag status data (active, stale, inactive)
     */
    private function getFlagStatusData(Team $team): array
    {
        // Active flags (last seen in the last 30 days)
        $activeFlags = FeatureFlag::where('team_id', $team->id)
            ->where('status', true)
            ->whereNotNull('last_seen_at')
            ->whereDate('last_seen_at', '>=', now()->subDays(30))
            ->count();

        // Stale flags (last seen more than 30 days ago)
        $staleFlags = FeatureFlag::where('team_id', $team->id)
            ->where('status', true)
            ->whereNotNull('last_seen_at')
            ->whereDate('last_seen_at', '<', now()->subDays(30))
            ->count();

        // Inactive flags (never seen)
        $inactiveFlags = FeatureFlag::where('team_id', $team->id)
            ->where('status', true)
            ->whereNull('last_seen_at')
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
    private function getFlagTypeData(Team $team): array
    {
        $featureTypes = FeatureType::where('team_id', $team->id)->get();

        $result = [];
        foreach ($featureTypes as $type) {
            $count = FeatureFlag::where('team_id', $team->id)
                ->where('feature_type_id', $type->id)
                ->count();

            $typeName = strtolower($type->name);
            $fill = match ($typeName) {
                'release' => 'var(--color-release)',
                'kill' => 'var(--color-kill)',
                'experiment' => 'var(--color-experiment)',
                default => 'var(--chart-' . (count($result) + 1) . ')',
            };

            $result[] = [
                'type' => $typeName,
                'flags' => $count,
                'fill' => $fill,
            ];
        }

        return $result;
    }

    /**
     * Get average age data by month
     */
    private function getAgeData(Team $team): array
    {
        $months = $this->getLastSixMonths();

        $result = [];
        foreach ($months as $month) {
            $startDate = Carbon::parse($month . '-01');
            $endDate = Carbon::parse($month . '-01')->endOfMonth();

            // Calculate average age in days for flags that existed in this month
            // Using database-agnostic date difference calculation
            $query = FeatureFlag::where('team_id', $team->id)
                ->where(function ($query) use ($startDate) {
                    $query->whereNull('created_at')
                        ->orWhere('created_at', '<=', $startDate->endOfMonth());
                });

            // Different DB engines handle date differences differently
            $connection = DB::connection()->getDriverName();
            if ($connection === 'pgsql') {
                // PostgreSQL date difference
                $averageAge = $query->selectRaw('AVG(DATE_PART(\'day\', ?::timestamp - created_at::timestamp)) as avg_age', [$endDate->format('Y-m-d')])
                    ->first()
                    ->avg_age ?? 0;
            } elseif ($connection === 'sqlite') {
                // SQLite date difference (in days)
                $averageAge = $query->selectRaw('AVG(julianday(?) - julianday(created_at)) as avg_age', [$endDate->format('Y-m-d')])
                    ->first()
                    ->avg_age ?? 0;
            } else {
                // MySQL/MariaDB (default)
                $averageAge = $query->selectRaw('AVG(DATEDIFF(?, created_at)) as avg_age', [$endDate->format('Y-m-d')])
                    ->first()
                    ->avg_age ?? 0;
            }

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
    private function getUsageOverTimeData(Team $team): array
    {
        $result = [];

        // Get active flags over time
        $activeData = LaravelMetrics::query(
            FeatureFlag::where('team_id', $team->id)
                ->where('status', true)
        )
            ->count()
            ->byMonth(6)
            ->fillMissingData()
            ->trends();

        // Get inactive flags over time
        $inactiveData = LaravelMetrics::query(
            FeatureFlag::where('team_id', $team->id)
                ->where('status', false)
        )
            ->count()
            ->byMonth(6)
            ->fillMissingData()
            ->trends();

        debug($activeData);
        debug($inactiveData);

        $activeData = collect($activeData['labels'])->combine($activeData['data']);
        $inactiveData = collect($inactiveData['labels'])->combine($inactiveData['data']);

        // Combine the two datasets
        foreach ($activeData as $month => $value) {
            $result[] = [
                'month' => $month,
                'active' => $value,
                'inactive' => $inactiveData[$month] ?? 0,
            ];
        }

        return $result;
    }

    /**
     * Get usage data for top flags
     */
    private function getUsageData(Team $team): array
    {
        // This will depend on how you track usage - this is a placeholder implementation
        // assuming you might store usage count in the last_seen_at field or a separate table
        return FeatureFlag::where('team_id', $team->id)
            ->whereNotNull('last_seen_at')
            ->orderBy('last_seen_at', 'desc')
            ->limit(6)
            ->get()
            ->map(function ($flag) {
                // Generate a random usage number for demonstration
                // In real implementation, you would get this from your tracking system
                return [
                    'application' => $flag->applications->first()?->name ?? 'Application',
                    'name' => $flag->name,
                    'usages' => rand(500, 1000), // Replace with actual usage data
                ];
            })
            ->toArray();
    }

    /**
     * Get oldest flags data
     */
    private function getOldestFlags(Team $team): array
    {
        return FeatureFlag::where('team_id', $team->id)
            ->orderBy('created_at', 'asc')
            ->limit(6)
            ->get()
            ->map(function ($flag) {
                return [
                    'application' => $flag->applications->first()?->name ?? 'Application',
                    'name' => $flag->name,
                    'created_at' => $flag->created_at->format('Y-m-d'),
                ];
            })
            ->toArray();
    }

    /**
     * Helper to get the last six months in Y-m format
     */
    private function getLastSixMonths(): array
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $months[] = now()->subMonths($i)->format('Y-m');
        }

        return $months;
    }
}
