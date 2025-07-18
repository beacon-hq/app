<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\FeatureFlagEvaluatedEvent;
use App\Events\FeatureFlagMissedEvent;
use App\Repositories\FeatureFlagRepository;
use App\Repositories\FeatureFlagStatusRepository;
use App\Values\FeatureFlag;
use App\Values\FeatureFlagContext;
use App\Values\FeatureFlagResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FeatureFlagStatusService
{
    public function __construct(protected FeatureFlagRepository $featureFlagRepository, protected FeatureFlagStatusRepository $featureFlagStatusRepository)
    {
    }

    public function hasStatus(FeatureFlag $featureFlag, FeatureFlagContext $context): bool
    {
        try {
            $this->featureFlagRepository->first(filters: [
                'id' => $featureFlag->id,
                'application' => $context->appName,
                'environment' => $context->environment,
            ]);
        } catch (ModelNotFoundException) {
            return false;
        }

        return true;
    }

    public function getStatus(FeatureFlag $featureFlag, FeatureFlagContext $context): FeatureFlagResponse
    {
        try {
            $featureFlag = $this->featureFlagRepository->first(filters: [
                'id' => $featureFlag->id,
                'application' => $context->appName,
                'environment' => $context->environment,
            ]);
        } catch (ModelNotFoundException) {
            $response = FeatureFlagResponse::from($featureFlag->name, null, false);

            FeatureFlagMissedEvent::dispatch(FeatureFlag::from(name: $featureFlag->name), $context, $response);

            return $response;
        }

        $response = $this->featureFlagStatusRepository->evaluate($featureFlag, $context);

        // Dispatch the feature flag evaluation event
        FeatureFlagEvaluatedEvent::dispatch(
            FeatureFlag::from($featureFlag),
            $context,
            $response,
        );

        return $response;
    }
}
