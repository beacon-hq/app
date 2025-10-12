<?php

declare(strict_types=1);

namespace App\Actions\FeatureFlags;

use App\Actions\Traits\AsAction;
use App\Models\FeatureFlag;
use App\Values\FeatureFlag as FeatureFlagValue;

class TouchFeatureFlag
{
    use AsAction;

    public function handle(FeatureFlagValue $featureFlag): FeatureFlagValue
    {
        $flag = FeatureFlag::query()->where('id', $featureFlag->id)->firstOrFail();

        $flag->update(['last_seen_at' => now()]);

        $flag->load(['tags', 'statuses', 'statuses.application', 'statuses.environment']);

        return FeatureFlagValue::from($flag);
    }
}
