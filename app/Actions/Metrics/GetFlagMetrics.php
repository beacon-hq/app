<?php

declare(strict_types=1);

namespace App\Actions\Metrics;

use App\Actions\Traits\AsAction;
use App\Models\FeatureFlag;
use App\Models\FeatureFlagUsage;
use Beacon\Metrics\Metrics;
use Beacon\Metrics\Values\Collections\TrendMetricCollection;
use Illuminate\Support\Collection;

class GetFlagMetrics
{
    use AsAction;

    public function handle(string $featureFlagId, ?string $applicationId = null, ?string $environmentId = null): array
    {
        FeatureFlag::findOrFail($featureFlagId);

        $evaluations = $this->getUsageMetrics($featureFlagId, $applicationId, $environmentId);
        $variants = $this->getVariantMetrics($featureFlagId, $applicationId, $environmentId);

        return [
            'evaluations' => $evaluations,
            'variants' => $variants,
        ];
    }

    protected function getUsageMetrics(string $featureFlagId, ?string $applicationId = null, ?string $environmentId = null): Collection
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

    protected function getVariantMetrics(string $featureFlagId, ?string $applicationId = null, ?string $environmentId = null): array
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
}
