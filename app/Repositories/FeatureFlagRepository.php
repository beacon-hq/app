<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\FeatureFlag;
use App\Models\FeatureFlagStatus;
use App\Services\ApplicationService;
use App\Services\EnvironmentService;
use App\Services\FeatureTypeService;
use App\Services\TagService;
use App\Values\FeatureFlag as FeatureFlagValue;
use App\Values\FeatureFlagStatus as FeatureFlagStatusValue;
use App\Values\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class FeatureFlagRepository
{
    public function __construct(
        protected FeatureTypeService $featureTypeService,
        protected TagService $tagService,
        protected ApplicationService $applicationService,
        protected EnvironmentService $environmentService,
    ) {
    }

    public function all(array|string $orderBy = ['name'], ?int $page = null, int $perPage = 20, array $filters = []): Collection
    {
        $query = $this->buildQuery($orderBy, $filters, $page, $perPage);

        return $query->get();
    }

    public function first(array $filters): FeatureFlag
    {
        $query = $this->buildQuery(filters: $filters);

        return $query->firstOrFail();
    }

    public function create(FeatureFlagValue $featureFlag): FeatureFlag
    {
        $data = $featureFlag
            ->toCollection()
            ->put('feature_type_id', $featureFlag->featureType->id)
            ->except('id', 'tags', 'feature_type', 'created_at', 'updated_at', 'last_seen_at', 'statuses')
            ->toArray();

        $flag = FeatureFlag::create($data);

        if ($featureFlag->tags !== null && count($featureFlag->tags) > 0) {
            $tags = $this->tagService->find(... $featureFlag->tags->pluck('id')->toArray());

            $flag->tags()->sync($tags instanceof Tag ? [$tags->id] : $tags->pluck('id'));
        }

        return $flag;
    }

    public function find(string $id): FeatureFlag
    {
        return FeatureFlag::with(['tags', 'statuses'])->where('id', $id)->firstOrFail();
    }

    public function update(FeatureFlagValue $featureFlag): FeatureFlag
    {
        $flag = FeatureFlag::where('id', $featureFlag->id)->firstOrFail();

        $data = $featureFlag
            ->toCollection()
            ->put('feature_type_id', $featureFlag->featureType->id)
            ->except('name', 'id', 'tags', 'feature_type', 'created_at', 'updated_at')
            ->filter(fn ($value, $key) => $key !== 'last_seen_at' || $value !== null)
            ->toArray();

        $flag->update($data);

        $tags = $featureFlag->tags->pluck('id')->toArray();
        if (count($tags) > 0) {
            $tags = $this->tagService->find(... $tags);
            $flag->tags()->sync($tags instanceof Tag ? [$tags->id] : $tags->pluck('id'));
        }

        if ($featureFlag->statuses?->count() > 0) {
            $statuses = $featureFlag->statuses->map(function (FeatureFlagStatusValue $status) use ($featureFlag) {
                return FeatureFlagStatus::updateOrCreate([
                    'id' => $status->id,
                ], [
                    'application_id' => $status->application->id,
                    'environment_id' => $status->environment->id,
                    'feature_flag_id' => $featureFlag->id,
                    'status' => $status->status,
                    'definition' => $status->definition,
                ]);
            });

            $flag->statuses()->sync($statuses->pluck('id'));

            FeatureFlagStatus::where('feature_flag_id', $featureFlag->id)->whereNotIn('id', $statuses->pluck('id'))->delete();
        }

        return $flag->fresh(['tags', 'statuses', 'statuses.application', 'statuses.environment']);
    }

    public function count(array $filters = []): int
    {
        return $this->buildQuery(filters: $filters)->count();
    }

    public function activityLog(string $id): Collection
    {
        return FeatureFlag::findOrFail($id)
            ->activities()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * @param Builder<FeatureFlag> $query
     */
    protected function filter(Builder $query, array $filters = []): Builder
    {
        $query
            ->when(isset($filters['name'][0]), fn (Builder $query) => $query->whereName($filters['name'][0]))
            ->when(isset($filters['tag']), fn (Builder $query) => $query->whereTags($filters['tag']))
            ->when(isset($filters['application']), fn (Builder $query) => $query->whereApplication($filters['application']))
            ->when(isset($filters['environment']), fn (Builder $query) => $query->whereEnvironment($filters['environment']))
            ->when(isset($filters['featureType']), fn (Builder $query) => $query->whereFeatureType($filters['featureType']));

        return $query;
    }

    protected function buildQuery(array|string|null $orderBy = null, array $filters = [], ?int $page = null, int $perPage = 20): Builder
    {
        $query = FeatureFlag::select('feature_flags.*')
            ->with(['featureType', 'tags', 'statuses', 'statuses.application', 'statuses.environment']);

        foreach (Arr::wrap($orderBy) as $column) {
            $query = $query->orderBy($column);
        }

        if (!empty($filters)) {
            $query = $this->filter($query, $filters);
        }

        if ($page !== null) {
            $query = $query->forPage($page, $perPage);
        }

        return $query;
    }
}
