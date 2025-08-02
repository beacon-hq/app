<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Activity;
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
        return FeatureFlag::with([
            'tags',
            'statuses' => function ($query) {
                $query->join('applications', 'feature_flag_statuses.application_id', '=', 'applications.id')
                    ->join('environments', 'feature_flag_statuses.environment_id', '=', 'environments.id')
                    ->orderBy('applications.name')
                    ->orderBy('environments.name')
                    ->select('feature_flag_statuses.*');
            },
            'statuses.application',
            'statuses.environment'
        ])->where('id', $id)->firstOrFail();
    }

    public function findByName(string $name): FeatureFlag
    {
        return FeatureFlag::with([
            'tags',
            'statuses' => function ($query) {
                $query->join('applications', 'feature_flag_statuses.application_id', '=', 'applications.id')
                    ->join('environments', 'feature_flag_statuses.environment_id', '=', 'environments.id')
                    ->orderBy('applications.name')
                    ->orderBy('environments.name')
                    ->select('feature_flag_statuses.*');
            },
            'statuses.application',
            'statuses.environment'
        ])->where('name', $name)->firstOrFail();
    }

    public function touch(FeatureFlagValue $featureFlag): FeatureFlag
    {
        $flag = FeatureFlag::where('id', $featureFlag->id)->firstOrFail();

        $flag->update(['last_seen_at' => now()]);

        return $flag->fresh(['tags', 'statuses', 'statuses.application', 'statuses.environment']);
    }

    public function update(FeatureFlagValue $featureFlag): FeatureFlag
    {
        $flag = FeatureFlag::where('id', $featureFlag->id)->firstOrFail();

        $data = $featureFlag
            ->toCollection()
            ->when($featureFlag->has('featureType') && $featureFlag->featureType !== null, fn ($collection) => $collection->put('feature_type_id', $featureFlag->featureType->id))
            ->when(!$featureFlag->has('featureType') || $featureFlag->featureType === null, fn ($collection) => $collection->put('feature_type_id', $flag->feature_type_id))
            ->except('name', 'id', 'tags', 'feature_type', 'created_at', 'updated_at', 'statuses')
            ->filter(fn ($value, $key) => $key !== 'last_seen_at' || $value !== null)
            ->toArray();

        $flag->update($data);

        $tags = $featureFlag->tags->pluck('id')->toArray();
        if (count($tags) > 0) {
            $tags = $this->tagService->find(... $tags);
            $flag->tags()->sync($tags instanceof Tag ? [$tags->id] : $tags->pluck('id'));
        }

        $filteredStatuses = $featureFlag->statuses?->filter(function (FeatureFlagStatusValue $status) {
            return $status->application !== null && $status->environment !== null;
        });

        if ($filteredStatuses?->isNotEmpty()) {
            /** @var Collection $statuses */
            $statuses = $filteredStatuses->map(function (FeatureFlagStatusValue $status) use ($featureFlag) {
                return FeatureFlagStatus::updateOrCreate([
                    'id' => $status->has('id') ? $status->id : null,
                ], [
                    'application_id' => $status->application->id,
                    'environment_id' => $status->environment->id,
                    'feature_flag_id' => $featureFlag->id,
                    'status' => $status->status,
                    'definition' => $status->definition,
                    'rollout_percentage' => $status->rolloutPercentage,
                    'rollout_context' => $status->rolloutContext,
                    'rollout_strategy' => $status->rolloutStrategy,
                    'variants' => $status->variants,
                    'variant_strategy' => $status->variantStrategy,
                    'variant_context' => $status->variantContext,
                ]);
            });

            $flag->statuses()->sync($statuses->pluck('id'));

            FeatureFlagStatus::where('feature_flag_id', $featureFlag->id)->whereNotIn('id', $statuses->pluck('id'))->delete();
        } else {
            $flag->statuses()->delete();
        }

        return $flag->fresh(['tags', 'statuses', 'statuses.application', 'statuses.environment']);
    }

    public function count(array $filters = []): int
    {
        return $this->buildQuery(filters: $filters)->count();
    }

    public function activityLog(string $id): Collection
    {
        $featureFlag = FeatureFlag::findOrFail($id);

        $featureFlagActivities = $featureFlag
            ->activities()
            ->get();

        $featureFlagStatusActivities = Activity::query()
            ->where('log_name', 'feature_flag_status')
            ->whereHasMorph('subject', [FeatureFlagStatus::class], function ($query) use ($id) {
                $query->where('feature_flag_id', $id);
            })
            ->get();

        return $featureFlagActivities
            ->merge($featureFlagStatusActivities)
            ->sortByDesc('created_at')
            ->values();
    }

    /**
     * @param Builder<FeatureFlag> $query
     */
    protected function filter(Builder $query, array $filters = []): Builder
    {
        $query
            ->when(isset($filters['id']), fn (Builder $query) => $query->where('id', $filters['id']))
            ->when(isset($filters['name'][0]), fn (Builder $query) => $query->whereName($filters['name'][0]))
            ->when(isset($filters['name']) && !is_array($filters['name']), fn (Builder $query) => $query->whereName($filters['name']))
            ->when(isset($filters['tag']), fn (Builder $query) => $query->whereTags($filters['tag']))
            ->when(isset($filters['application']), fn (Builder $query) => $query->whereApplication($filters['application']))
            ->when(isset($filters['environment']), fn (Builder $query) => $query->whereEnvironment($filters['environment']))
            ->when(isset($filters['featureType']), fn (Builder $query) => $query->whereFeatureType($filters['featureType']))
            ->when(isset($filters['status']), fn (Builder $query) => $query->whereStatus($filters['status'] === 'active'));

        return $query;
    }

    protected function buildQuery(array|string|null $orderBy = null, array $filters = [], ?int $page = null, int $perPage = 20): Builder
    {
        $query = FeatureFlag::select('feature_flags.*')
            ->with(['featureType', 'tags', 'statuses', 'statuses.application', 'statuses.environment']);

        foreach (Arr::wrap($orderBy) as $column) {
            if (str_starts_with($column, '-')) {
                $query = $query->orderBy(substr($column, 1), 'desc');
            } else {
                $query = $query->orderBy($column);
            }
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
