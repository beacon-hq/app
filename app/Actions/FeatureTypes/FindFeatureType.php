<?php

declare(strict_types=1);

namespace App\Actions\FeatureTypes;

use App\Actions\Traits\AsAction;
use App\Models\FeatureType as FeatureTypeModel;
use App\Values\FeatureType;

class FindFeatureType
{
    use AsAction;

    public function __construct(
        protected GetDefaultFeatureType $getDefaultFeatureTypeAction
    ) {
    }

    public function handle(?string $id): FeatureType
    {
        if ($id === null) {
            return $this->getDefaultFeatureTypeAction->handle();
        }

        $featureType = FeatureTypeModel::findOrFail($id);

        return FeatureType::from($featureType);
    }
}
