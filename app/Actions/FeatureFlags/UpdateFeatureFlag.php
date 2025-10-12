<?php

declare(strict_types=1);

namespace App\Actions\FeatureFlags;

use App\Actions\Traits\AsAction;
use App\Models\FeatureFlag;
use App\Models\FeatureFlagStatus;
use App\Values\FeatureFlag as FeatureFlagValue;
use App\Values\FeatureFlagStatus as FeatureFlagStatusValue;
use Bag\Values\Optional;
use Illuminate\Database\Eloquent\Collection;

class UpdateFeatureFlag
{
    use AsAction;

    public function handle(FeatureFlagValue $featureFlag): FeatureFlagValue
    {
        // Find the feature flag to update
        $flag = FeatureFlag::query()->where('id', $featureFlag->id)->firstOrFail();

        // Extract data from the value object
        $data = $featureFlag
            ->toCollection()
            ->when(
                $featureFlag->has('featureType') && $featureFlag->featureType !== null,
                fn ($collection) => $collection->put('feature_type_id', $featureFlag->featureType->id)
            )
            ->when(
                !$featureFlag->has('featureType') || $featureFlag->featureType === null,
                fn ($collection) => $collection->put('feature_type_id', $flag->feature_type_id)
            )
            ->except('name', 'id', 'tags', 'feature_type', 'created_at', 'updated_at', 'statuses')
            ->filter(fn ($value, $key) => $key !== 'last_seen_at' || $value !== null || !($value instanceof Optional))
            ->toArray();

        // Update the feature flag
        $flag->update($data);

        // Update tags if provided
        if ($featureFlag->has('tags')) {
            $tagIds = $featureFlag->tags->pluck('id')->toArray();
            $flag->tags()->sync($tagIds);
        } else {
            $flag->tags()->detach();
        }

        // Update statuses if provided
        if ($featureFlag->has('statuses')) {
            $filteredStatuses = $featureFlag->statuses?->filter(function (FeatureFlagStatusValue $status) {
                return $status->application !== null && $status->environment !== null;
            });

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

            FeatureFlagStatus::query()
                ->where('feature_flag_id', $featureFlag->id)
                ->when($statuses->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $statuses->pluck('id')))
                ->delete();
        } else {
            $flag->statuses()->delete();
        }

        // Load relationships and return the value object
        $flag->refresh();
        $flag->load(['tags', 'statuses', 'statuses.application', 'statuses.environment']);

        return FeatureFlagValue::from($flag);
    }
}
