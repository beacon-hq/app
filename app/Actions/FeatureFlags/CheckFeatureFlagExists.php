<?php

declare(strict_types=1);

namespace App\Actions\FeatureFlags;

use App\Actions\Traits\AsAction;
use App\Models\FeatureFlag;
use App\Values\FeatureFlag as FeatureFlagValue;
use App\Values\FeatureFlagContext;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CheckFeatureFlagExists
{
    use AsAction;

    public function handle(FeatureFlagValue $featureFlag, FeatureFlagContext $context): bool
    {
        try {
            FeatureFlag::query()
                ->where('name', $featureFlag->name)
                ->whereApplication($context->appName)
                ->whereEnvironment($context->environment)
                ->firstOrFail();

            return true;
        } catch (ModelNotFoundException) {
            return false;
        }
    }
}
