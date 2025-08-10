<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Activity;
use App\Models\FeatureFlag;
use App\Models\FeatureFlagUsage;
use App\Services\SubscriptionBillingService;
use Beacon\Metrics\Metrics;
use Beacon\Metrics\Values\Collections\TrendMetricCollection;
use Beacon\Metrics\Values\TrendMetric;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use stdClass;

class MetricsRepository
{
    public function __construct(protected SubscriptionBillingService $subscriptionBillingService)
    {
    }

    public function getTotalFlags(): array
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

    public function getChangesMetrics(): array
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

    public function getCreatedMetrics(): array
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

    public function getArchivedMetrics(): array
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

    public function getFlagTypeData(): array
    {
        $metrics = FeatureFlag::select([DB::raw('COUNT(feature_flags.id) as flags'), 'feature_types.id', 'feature_types.name as type', 'feature_types.color as fill'])
            ->join('feature_types', 'feature_types.id', '=', 'feature_flags.feature_type_id')
            ->groupBy('feature_types.id', 'feature_types.name', 'feature_types.color')->get();


        return [
            'data' => $metrics->toArray(),
            'total' => $metrics->sum('flags')
        ];
    }

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
                ->where('feature_flag_usages.team_id', App::context()->team->id)
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
            ->where('feature_flags.team_id', App::context()->team->id)
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
            ->where('feature_flags.team_id', App::context()->team->id)
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

    public function getPlanUsage(): Collection
    {
        $currentPeriodEnd = $this->subscriptionBillingService->getPeriodEndDate(App::context()->organization);

        $query = FeatureFlagUsage::query();

        $metrics = Metrics::query($query)
            ->countByDay()
            ->dateColumn('evaluated_at')
            ->projectForDate($currentPeriodEnd)
            ->projectWhen($this->subscriptionBillingService->getPlan(App::context()->organization)->entitlements['evaluations'])
            ->between($this->subscriptionBillingService->getPeriodStartDate(App::context()->organization), now())
            ->trends();

        $data = collect([
            'data' => $metrics->assoc(),
            'total' => $metrics->total,
            'projections' => $metrics->projections->toArray() ?? [],
        ]);


        if ($metrics->projections->when->projectedDate?->greaterThan($currentPeriodEnd)) {
            $data['projections'] = $metrics->projections->toCollection()->only('data')->toArray();
        }

        return $data;
    }

    public function getFlagLifeCycleMetrics(): array
    {
        $unused = $this->getUnusedFlagMetrics();
        $active = $this->getActiveFlagMetrics();
        $inactive = $this->getInactiveFlagMetrics();
        $stale = $this->getStaleFlagMetrics();

        return [
            ['state' => 'unused', 'flags' => $unused->total, 'fill' => 'var(--color-unused)'],
            ['state' => 'active', 'flags' => $active->total, 'fill' => 'var(--color-active)'],
            ['state' => 'stale', 'flags' => $stale->total, 'fill' => 'var(--color-stale)'],
            ['state' => 'inactive', 'flags' => $inactive->total, 'fill' => 'var(--color-inactive)'],
        ];
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

    public function getFlagMetrics(string $featureFlagId, ?string $applicationId = null, ?string $environmentId = null): array
    {
        return [
            'evaluations' => $this->getUsageMetrics($featureFlagId, $applicationId, $environmentId),
            'variants' => $this->getVariantMetrics($featureFlagId, $applicationId, $environmentId),
        ];
    }

    public function getUsageMetrics(string $featureFlagId, ?string $applicationId = null, ?string $environmentId = null): Collection
    {
        $query = FeatureFlagUsage::query()
            ->where('feature_flag_id', $featureFlagId);

        if ($applicationId) {
            $query->where('application_id', $applicationId);
        }

        if ($environmentId) {
            $query->where('environment_id', $environmentId);
        }

        /** @var TrendMetricCollection $metrics */
        $metrics = Metrics::query($query)
            ->countByDay()
            ->groupBy('active')
            ->dateColumn('evaluated_at')
            ->from(
                now()->subDays(30),
            )
            ->trends();

        if ($metrics->isEmpty()) {
            return collect([
                'data' => [],
                'total' => 0,
            ]);
        }

        return collect([
            'data' => Collection::empty()
                ->when(isset($metrics[1]) && $metrics[1]->labels->isNotEmpty(), function (Collection $collection) use ($metrics) {
                    return $collection->concat($metrics[1]->labels ?? []);
                })
                ->when(isset($metrics[0]) && $metrics[0]->labels->isNotEmpty(), function (Collection $collection) use ($metrics) {
                    return $collection->concat($metrics[0]->labels ?? []);
                })
                ->unique()
                ->map(function (string $label, int $index) use ($metrics) {
                    return [
                        'date' => $label,
                        'active' => $metrics[1]->data[$index] ?? 0,
                        'inactive' => $metrics[0]->data[$index] ?? 0,
                        'total' => ($metrics[1]->data[$index] ?? 0) + ($metrics[0]->data[$index] ?? 0),
                    ];
                })
                ->toArray(),
            'total' => ($metrics[1]->total ?? 0) + ($metrics[0]->total ?? 0),
        ]);
    }

    public function getVariantMetrics(string $featureFlagId, ?string $applicationId = null, ?string $environmentId = null): array
    {
        $query = FeatureFlagUsage::query()
            ->where('feature_flag_id', $featureFlagId);

        if ($applicationId) {
            $query->where('application_id', $applicationId);
        }

        if ($environmentId) {
            $query->where('environment_id', $environmentId);
        }

        $chartColors = ['chart-1', 'chart-2', 'chart-3', 'chart-4', 'chart-5'];

        $metrics = Metrics::query($query)
            ->count()
            ->when(config('database.default') === 'sqlite', fn (Metrics $query) => $query->groupBy('value'))
            ->when(config('database.default') !== 'sqlite', fn (Metrics $query) => $query->groupBy('value::text'))
            ->dateColumn('evaluated_at')
            ->from(
                now()->subDays(30),
            )
            ->value();

        $total = $metrics->sum('value');

        return [
            'data' => $metrics->map(function ($value, $key) use ($total) {
                $percentage = (float) \number_format(($value->value / $total) * 100, 2);
                if ($key === '') {
                    return ['value' => 'No Value', 'count' => $value->value, 'percentage' => $percentage];
                }

                if (Str::startsWith($key, '"') && Str::endsWith($key, '"')) {
                    $key = Str::substr($key, 1, -1);

                    return ['value' => $key, 'count' => $value->value, 'percentage' => $percentage];
                }

                return ['value' => $key, 'count' => $value->value, 'percentage' => $percentage];
            })
            ->values()
            ->map(function ($variant, $index) use ($chartColors) {
                if ($index < count($chartColors)) {
                    $color = 'var(--' . $chartColors[$index] . ')';
                } else {
                    $color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
                }

                return [
                    'value' => $variant['value'],
                    'percentage' => $variant['percentage'],
                    'count' => $variant['count'],
                    'fill' => $color,
                ];
            })
            ->toArray(),
            'total' => $total,
        ];
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
