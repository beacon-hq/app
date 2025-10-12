<?php

declare(strict_types=1);

namespace App\Actions\FeatureTypes;

use App\Actions\Traits\AsAction;
use App\Models\FeatureType as FeatureTypeModel;
use App\Values\FeatureType;

class FindFeatureTypeByName
{
    use AsAction;

    public function handle(string $name): FeatureType
    {
        $featureType = FeatureTypeModel::where('name', $name)->firstOrFail();

        return FeatureType::from($featureType);
    }
}
