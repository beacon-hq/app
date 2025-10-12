<?php

declare(strict_types=1);

namespace App\Actions\FeatureFlags;

use App\Actions\Traits\AsAction;
use App\Models\FeatureFlag;
use App\Values\Collections\FeatureFlagCollection;
use App\Values\FeatureFlag as FeatureFlagValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class GetFeatureFlags
{
    use AsAction;

    public function handle(?int $page = null, int $perPage = 20, array $filters = [], array $sort = []): FeatureFlagCollection
    {
        $query = FeatureFlag::query()
            ->with(['featureType', 'tags', 'statuses', 'statuses.application', 'statuses.environment']);

        // Apply sorting
        $orderBy = ['name']; // Default ordering
        if (!empty($sort)) {
            $orderBy = [];
            foreach ($sort as $column => $direction) {
                $orderBy[] = $direction === 'desc' ? "-{$column}" : $column;
            }
        }

        foreach (Arr::wrap($orderBy) as $column) {
            if (str_starts_with($column, '-')) {
                $query->orderBy(substr($column, 1), 'desc');
            } else {
                $query->orderBy($column);
            }
        }

        // Apply filters
        $this->applyFilters($query, $filters);

        // Apply pagination
        if ($page !== null) {
            $query->forPage($page, $perPage);
        }

        // Execute query and transform to value objects
        $featureFlags = $query->get();

        return FeatureFlagValue::collect($featureFlags);
    }

    protected function applyFilters(Builder $query, array $filters = []): void
    {
        $query
            ->when(isset($filters['id']), fn (Builder $query) => $query->where('id', $filters['id']))
            ->when(isset($filters['name'][0]), fn (Builder $query) => $query->whereName($filters['name'][0]))
            ->when(isset($filters['name']) && !is_array($filters['name']), fn (Builder $query) => $query->whereName($filters['name']))
            ->when(isset($filters['tag']), fn (Builder $query) => $query->whereTags(Arr::wrap($filters['tag'])))
            ->when(isset($filters['application']), fn (Builder $query) => $query->whereApplication($filters['application']))
            ->when(isset($filters['environment']), fn (Builder $query) => $query->whereEnvironment($filters['environment']))
            ->when(isset($filters['featureType']), fn (Builder $query) => $query->whereFeatureType($filters['featureType']))
            ->when(isset($filters['status']), fn (Builder $query) => $query->whereStatus($filters['status'] === 'active'));
    }
}
