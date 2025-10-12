<?php

declare(strict_types=1);

namespace App\Actions\FeatureTypes;

use App\Actions\AppContext\GetAppContext;
use App\Actions\Traits\AsAction;
use App\Models\FeatureType as FeatureTypeModel;
use App\Values\Collections\FeatureTypeCollection;
use App\Values\FeatureType;

class ListFeatureTypes
{
    use AsAction;

    public function handle(): FeatureTypeCollection
    {
        $featureTypes = FeatureTypeModel::query()
            ->where('team_id', GetAppContext::team()->id)
            ->orderBy('name')
            ->get();

        return FeatureType::collect($featureTypes);
    }
}
