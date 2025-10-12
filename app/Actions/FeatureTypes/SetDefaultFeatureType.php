<?php

declare(strict_types=1);

namespace App\Actions\FeatureTypes;

use App\Actions\Traits\AsAction;
use App\Models\FeatureType as FeatureTypeModel;
use App\Values\FeatureType;
use Illuminate\Support\Facades\DB;

class SetDefaultFeatureType
{
    use AsAction;

    public function handle(string $id): FeatureType
    {
        return DB::transaction(function () use ($id) {
            $featureType = FeatureTypeModel::findOrFail($id);

            FeatureTypeModel::where('id', '!=', $id)
                ->where('is_default', true)
                ->update(['is_default' => false]);

            $featureType->is_default = true;
            $featureType->save();

            return FeatureType::from($featureType);
        });
    }
}
