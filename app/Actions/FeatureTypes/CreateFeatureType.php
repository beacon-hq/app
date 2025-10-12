<?php

declare(strict_types=1);

namespace App\Actions\FeatureTypes;

use App\Actions\AppContext\GetAppContext;
use App\Actions\Traits\AsAction;
use App\Models\FeatureType as FeatureTypeModel;
use App\Values\FeatureType;

/**
 * @method static FeatureType run(FeatureType $featureType)
 */
class CreateFeatureType
{
    use AsAction;

    public function handle(FeatureType $featureType): FeatureType
    {
        $data = $featureType->toArray();

        $model = new FeatureTypeModel();
        $model->name = $data['name'] ?? null;
        $model->description = $data['description'] ?? null;
        $model->is_default = $data['is_default'] ?? false;
        $model->temporary = $data['temporary'] ?? false;
        $model->color = $data['color'] ?? '#e3e3e3';
        $model->icon = $data['icon'] ?? null;
        $model->team_id = GetAppContext::team()->id;
        $model->save();

        return FeatureType::from($model);
    }
}
