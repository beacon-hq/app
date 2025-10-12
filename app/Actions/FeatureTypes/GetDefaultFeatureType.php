<?php

declare(strict_types=1);

namespace App\Actions\FeatureTypes;

use App\Actions\AppContext\GetAppContext;
use App\Actions\Traits\AsAction;
use App\Models\FeatureType as FeatureTypeModel;
use App\Values\FeatureType;

class GetDefaultFeatureType
{
    use AsAction;

    public function handle(): FeatureType
    {
        $teamId = GetAppContext::team()->id;

        $featureType = FeatureTypeModel::query()
            ->where('team_id', $teamId)
            ->where('is_default', true)
            ->first();

        if (!$featureType) {
            $featureType = FeatureTypeModel::query()
                ->where('team_id', $teamId)
                ->first();

            if (!$featureType) {
                throw new \RuntimeException('No feature types found in the system.');
            }
        }

        return FeatureType::from($featureType);
    }
}
