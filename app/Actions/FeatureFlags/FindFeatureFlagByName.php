<?php

declare(strict_types=1);

namespace App\Actions\FeatureFlags;

use App\Actions\Traits\AsAction;
use App\Models\FeatureFlag;
use App\Values\FeatureFlag as FeatureFlagValue;

class FindFeatureFlagByName
{
    use AsAction;

    public function handle(string $name): FeatureFlagValue
    {
        $featureFlag = FeatureFlag::with([
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

        return FeatureFlagValue::from($featureFlag);
    }
}
