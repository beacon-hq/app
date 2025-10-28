<?php

declare(strict_types=1);

namespace App\Actions\FeatureFlags;

use App\Actions\Traits\AsAction;
use App\Models\Activity;
use App\Models\FeatureFlag;
use App\Models\FeatureFlagStatus;
use App\Values\ActivityLog;
use App\Values\Collections\ActivityLogCollection;

/**
 * @method static ActivityLogCollection run(string $featureFlagId)
 */
class GetFeatureFlagActivityLog
{
    use AsAction;

    public function handle(string $featureFlagId): ActivityLogCollection
    {
        $featureFlag = FeatureFlag::findOrFail($featureFlagId);

        $featureFlagActivities = $featureFlag
            ->activities()
            ->get();

        $featureFlagStatusActivities = Activity::query()
            ->where('log_name', 'feature_flag_status')
            ->whereHasMorph('subject', [FeatureFlagStatus::class], function ($query) use ($featureFlagId) {
                $query->where('feature_flag_id', $featureFlagId);
            })
            ->get();

        $mergedActivities = $featureFlagActivities
            ->merge($featureFlagStatusActivities)
            ->sortByDesc('created_at')
            ->values();

        return ActivityLog::collect($mergedActivities);
    }
}
