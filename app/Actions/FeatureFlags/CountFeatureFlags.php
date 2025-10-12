<?php

declare(strict_types=1);

namespace App\Actions\FeatureFlags;

use App\Actions\Traits\AsAction;
use App\Models\FeatureFlag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class CountFeatureFlags
{
    use AsAction;

    public function handle(array $filters = []): int
    {
        $query = FeatureFlag::query();

        $this->applyFilters($query, $filters);

        return $query->count();
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
            ->when(isset($filters['status']), fn (Builder $query) => $query->where('status', $filters['status'] === 'active'));
    }
}
