<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Events\FeatureFlagMissedEvent;
use App\Http\Controllers\Controller;
use App\Services\FeatureFlagService;
use App\Services\FeatureFlagStatusService;
use App\Values\Collections\FeatureFlagCollection;
use App\Values\FeatureFlag;
use App\Values\FeatureFlagContext;
use App\Values\FeatureFlagResponse;
use Bag\Attributes\WithoutValidation;
use Illuminate\Http\Request;

class FeatureFlagController extends Controller
{
    public function index(Request $request, FeatureFlagService $featureFlagService): FeatureFlagCollection
    {
        return $featureFlagService->all();
    }

    public function show(
        #[WithoutValidation]
        FeatureFlag $featureFlag,
        FeatureFlagService $featureFlagService,
        FeatureFlagStatusService $featureFlagStatusService,
        Request $request
    ): FeatureFlagResponse {
        try {
            $context = FeatureFlagContext::from(... $request->json()->all());

            $featureFlag = $featureFlagService->findByName($featureFlag->id);

            return $featureFlagStatusService->getStatus($featureFlag, $context);
        } catch (\Throwable $e) {
            $response = FeatureFlagResponse::from(
                featureFlag: $featureFlag->has('name') ? $featureFlag->name : $featureFlag->id,
                value: null,
                active: false,
            );

            if (!$featureFlag->has('name')) {
                $featureFlag = FeatureFlag::withoutValidation([
                    ...$featureFlag->toArray(),
                    'name' => $featureFlag->id
                ]);
            }

            FeatureFlagMissedEvent::dispatch($featureFlag, $context, $response);

            return $response;
        }
    }
}
