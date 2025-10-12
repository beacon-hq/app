<?php

declare(strict_types=1);

namespace App\Actions\FeatureTypes;

use App\Actions\Traits\AsAction;
use App\Models\FeatureType as FeatureTypeModel;
use App\Values\FeatureType;

class UpdateFeatureType
{
    use AsAction;

    public function handle(FeatureType $featureType): FeatureType
    {
        $model = FeatureTypeModel::findOrFail($featureType->id);
        $model->fill($featureType->toArray());
        $model->save();

        return FeatureType::from($model);
    }
}
