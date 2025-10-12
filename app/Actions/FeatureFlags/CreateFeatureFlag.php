<?php

declare(strict_types=1);

namespace App\Actions\FeatureFlags;

use App\Actions\Traits\AsAction;
use App\Models\FeatureFlag;
use App\Models\Tag;
use App\Values\FeatureFlag as FeatureFlagValue;

/**
 * @method static FeatureFlagValue run(FeatureFlagValue $featureFlag)
 */
class CreateFeatureFlag
{
    use AsAction;

    public function handle(FeatureFlagValue $featureFlag): FeatureFlagValue
    {
        // Extract data from the value object
        $data = $featureFlag
            ->toCollection()
            ->put('feature_type_id', $featureFlag->featureType?->id)
            ->except('id', 'tags', 'feature_type', 'created_at', 'updated_at', 'last_seen_at', 'statuses')
            ->toArray();

        // Create the feature flag
        $flag = FeatureFlag::create($data);

        // Sync tags if provided
        if ($featureFlag->has('tags') && count($featureFlag->tags) > 0) {
            $tagIds = $featureFlag->tags->pluck('id')->toArray();

            // Find all the tags
            $tags = Tag::query()->whereIn('id', $tagIds)->get();

            // Sync the tags with the feature flag
            $flag->tags()->sync($tags->pluck('id'));
        }

        // Load relationships and return the value object
        $flag->load(['tags', 'statuses', 'statuses.application', 'statuses.environment']);

        return FeatureFlagValue::from($flag);
    }
}
