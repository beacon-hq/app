<?php

declare(strict_types=1);

namespace App\Actions\FeatureFlags;

use App\Actions\Traits\AsAction;
use App\Models\FeatureFlag as FeatureFlagModel;
use App\Values\Collections\FeatureFlagCollection;
use App\Values\FeatureFlag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * @method static FeatureFlagCollection run(?int $page = null, int $perPage = 20, array $filters = [], array $sort = [])
 */
class ListFeatureFlags
{
    use AsAction;

    public function handle(?int $page = null, int $perPage = 20, array $filters = [], array $sort = []): FeatureFlagCollection
    {
        $query = FeatureFlagModel::query()
            ->with(['featureType', 'tags', 'statuses', 'statuses.application.environments']);

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
            $query->where(function (Builder $query) use ($filters) {
                foreach (Arr::wrap($filters['name']) as $name) {
                    $query->orWhereLike('name', '%' . $name . '%');
                }
            });
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
            $query->whereApplication($filters['application']);
        }

        if (isset($filters['environment'])) {
            $query->whereEnvironment($filters['environment']);
        }

        if (isset($filters['status'])) {
            $statuses = Collection::wrap($filters['status']);
            $query
                ->when($statuses->contains('open') && !$statuses->contains('complete'), function (Builder $query) {
                    $query->whereNull('completed_at');
                })
                ->when($statuses->contains('complete') && !$statuses->contains('open'), function (Builder $query) {
                    $query->whereNotNull('completed_at');
                })
                ->when($statuses->contains('enabled') && !$statuses->contains('disabled'), function (Builder $query) {
                    $query->where('status', true);
                })
                ->when($statuses->contains('disabled') && !$statuses->contains('enabled'), function (Builder $query) {
                    $query->where('status', false);
                });
        }

        return $query;
    }
}
