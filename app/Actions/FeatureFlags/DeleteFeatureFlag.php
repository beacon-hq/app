<?php

declare(strict_types=1);

namespace App\Actions\FeatureFlags;

use App\Actions\Traits\AsAction;
use App\Models\FeatureFlag as FeatureFlagModel;
use App\Models\FeatureFlagUsage;
use App\Values\FeatureFlag;

class DeleteFeatureFlag
{
    use AsAction;

    public function handle(FeatureFlag $featureFlag): void
    {
        $model = FeatureFlagModel::query()->findOrFail($featureFlag->id);

        FeatureFlagUsage::query()
            ->where('team_id', $model->team_id)
            ->where(function ($query) use ($model) {
                $query->where('feature_flag_id', $model->id)
                    ->orWhere('feature_flag_name', $model->name);
            })
            ->delete();

        $model->statuses()->delete();
        $model->tags()->detach();
        $model->delete();
    }
}
