<?php

declare(strict_types=1);

namespace App\Actions\FeatureFlags;

use App\Actions\Traits\AsAction;
use App\Models\FeatureFlag as FeatureFlagModel;
use App\Values\Collections\FeatureFlagCollection;
use App\Values\FeatureFlag;
use Illuminate\Database\Eloquent\Builder;

class ListFeatureFlags
{
    use AsAction;

    public function handle(?int $page = null, int $perPage = 20, array $filters = [], array $sort = []): FeatureFlagCollection
    {
        $query = FeatureFlagModel::query();

        // Apply filters
        if (!empty($filters)) {
            $query = $this->applyFilters($query, $filters);
        }

        // Apply sorting
        $orderBy = ['name']; // Default ordering
        if (!empty($sort)) {
            $orderBy = [];
            foreach ($sort as $column => $direction) {
                $orderBy[] = $direction === 'desc' ? "-{$column}" : $column;
            }
        }

        foreach ($orderBy as $field) {
            $direction = 'asc';
            if (str_starts_with($field, '-')) {
                $direction = 'desc';
                $field = substr($field, 1);
            }
            $query->orderBy($field, $direction);
        }

        // Pagination
        if ($page !== null) {
            $paginatedResult = $query->paginate($perPage, ['*'], 'page', $page);
            $featureFlags = $paginatedResult->items();
        } else {
            $featureFlags = $query->get();
        }

        return FeatureFlag::collect($featureFlags);
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (isset($filters['name'])) {
            $query->where('name', 'LIKE', "%{$filters['name']}%");
        }

        if (isset($filters['tag'])) {
            $query->whereHas('tags', function (Builder $query) use ($filters) {
                $query->where('tags.id', $filters['tag']);
            });
        }

        if (isset($filters['feature_type'])) {
            $query->where('feature_type_id', $filters['feature_type']);
        }

        if (isset($filters['application'])) {
            $query->whereExists(function ($subquery) use ($query, $filters) {
                $subquery->select('*')
                    ->from('feature_flag_statuses')
                    ->whereRaw('feature_flag_statuses.feature_flag_id = feature_flags.id')
                    ->where('feature_flag_statuses.application_id', $filters['application']);
            });
        }

        if (isset($filters['environment'])) {
            $query->whereExists(function ($subquery) use ($query, $filters) {
                $subquery->select('*')
                    ->from('feature_flag_statuses')
                    ->whereRaw('feature_flag_statuses.feature_flag_id = feature_flags.id')
                    ->where('feature_flag_statuses.environment_id', $filters['environment']);
            });
        }

        if (isset($filters['status'])) {
            $query->whereExists(function ($subquery) use ($query, $filters) {
                $subquery->select('*')
                    ->from('feature_flag_statuses')
                    ->whereRaw('feature_flag_statuses.feature_flag_id = feature_flags.id')
                    ->where('feature_flag_statuses.status', $filters['status']);
            });
        }

        return $query;
    }
}
