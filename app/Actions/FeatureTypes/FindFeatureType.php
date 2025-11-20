<?php

declare(strict_types=1);

namespace App\Actions\FeatureTypes;

use App\Actions\Traits\AsAction;
use App\Models\FeatureType as FeatureTypeModel;
use App\Values\FeatureType;

class FindFeatureType
{
    use AsAction;

    public function handle(?string $id): FeatureType
    {
        if ($id === null) {
            return GetDefaultFeatureType::run();
        }

        $featureType = FeatureTypeModel::findOrFail($id);

        return FeatureType::from($featureType);
    }
}
