<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\FeatureFlag;
use App\Models\FeatureFlagStatus;
use App\Services\ApplicationService;
use App\Services\EnvironmentService;
use App\Services\FeatureFlagService;
use App\Services\FeatureTypeService;
use App\Services\TagService;
use App\Values\Collections\FeatureFlagCollection;
use App\Values\FeatureFlag as FeatureFlagValue;
use App\Values\FeatureFlagStatus as FeatureFlagStatusValue;
use App\Values\Tag;
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

    public function all(array|string $orderBy = ['name'], ?int $page = null, int $perPage = 20, array $filters = []): FeatureFlagCollection
    {
        $query = FeatureFlag::with(['featureType', 'tags']);

        foreach (Arr::wrap($orderBy) as $column) {
            $query = $query->orderBy($column);
        }

        if (!empty($filters)) {
            $query = $query->where(function ($query) use ($filters) {
                foreach ($filters as $key => $value) {
                    if ($key === 'tags') {
                        $query->whereHas('tags', function ($query) use ($value) {
                            $query->whereIn('slug', Arr::wrap($value));
                        });
                    }

                    if ($key === 'name') {
                        $query->where('name', 'LIKE', "%$value%");
                    }
                }
            });
        }

        if ($page !== null) {
            $query = $query->forPage($page, $perPage);
        }

        return FeatureFlagValue::collect($query->get());
    }

    public function create(FeatureFlagValue $featureFlag): FeatureFlagValue
    {
        $data = $featureFlag
            ->toCollection()
            ->put('feature_type_id', $this->featureTypeService->findBySlug($featureFlag->featureType->slug)->id)
            ->except('slug', 'tags', 'feature_type', 'created_at', 'updated_at', 'last_seen_at', 'statuses')
            ->toArray();

        $flag = FeatureFlag::create($data);

        if ($featureFlag->tags !== null && count($featureFlag->tags) > 0) {
            $tags = $this->tagService->findBySlug(... $featureFlag->tags->pluck('slug')->toArray());

            $flag->tags()->sync($tags instanceof Tag ? [$tags->id] : $tags->pluck('id'));
        }

        return $featureFlag;
    }

    public function findBySlug(string $slug): FeatureFlagValue
    {
        return FeatureFlagValue::from(FeatureFlag::with('tags')->where('slug', $slug)->firstOrFail());
    }

    public function update(FeatureFlagValue $featureFlag): FeatureFlagValue
    {
        $flag = FeatureFlag::where('slug', $featureFlag->slug)->firstOrFail();

        $data = $featureFlag
            ->toCollection()
            ->put('feature_type_id', $this->featureTypeService->findBySlug($featureFlag->featureType->slug)->id)
            ->except('name', 'slug', 'tags', 'feature_type', 'created_at', 'updated_at', 'last_seen_at')
            ->toArray();

        $flag->update($data);

        $tags = $this->tagService->findBySlug(... $featureFlag->tags->pluck('slug')->toArray());
        $flag->tags()->sync($tags instanceof Tag ? [$tags->id] : $tags->pluck('id'));

        if ($featureFlag->statuses->count() > 0) {
            $featureFlagId = resolve(FeatureFlagService::class)->findBySlug($featureFlag->slug)->id;

            $statuses = $featureFlag->statuses->map(function (FeatureFlagStatusValue $status) use ($featureFlagId, $featureFlag) {
                return FeatureFlagStatus::updateOrCreate([
                    'id' => $status->id,
                ], [
                    'application_id' => $this->applicationService->findBySlug($status->application->slug)->id,
                    'environment_id' => $this->environmentService->findBySlug($status->environment->slug)->id,
                    'feature_flag_id' => $featureFlagId,
                    'status' => $status->status,
                ]);
            });

            $flag->statuses()->sync($statuses->pluck('id'));

            FeatureFlagStatus::where('feature_flag_id', $featureFlagId)->whereNotIn('id', $statuses->pluck('id'))->delete();
        }

        return $featureFlag;
    }

    public function count()
    {
        return FeatureFlag::count();
    }
}
