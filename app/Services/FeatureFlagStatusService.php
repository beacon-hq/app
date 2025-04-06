<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\FeatureFlagRepository;
use App\Repositories\FeatureFlagStatusRepository;
use App\Values\FeatureFlag;
use App\Values\FeatureFlagContext;
use App\Values\FeatureFlagResponse;

class FeatureFlagStatusService
{
    public function __construct(protected FeatureFlagRepository $featureFlagRepository, protected FeatureFlagStatusRepository $featureFlagStatusRepository)
    {
    }

    public function getStatus(FeatureFlag $featureFlag, FeatureFlagContext $context): FeatureFlagResponse
    {
        $featureFlag = $this->featureFlagRepository->first(filters: [
            'id' => $featureFlag->id,
            'application' => $context->appName,
            'environment' => $context->environment,
        ]);

        return $this->featureFlagStatusRepository->first($featureFlag, $context);
    }
}
