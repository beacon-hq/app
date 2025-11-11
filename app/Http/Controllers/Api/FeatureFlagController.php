<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\FeatureFlags\GetFeatureFlagStatus;
use App\Actions\FeatureFlags\ListFeatureFlags;
use App\Events\FeatureFlagMissedEvent;
use App\Values\FeatureFlag;
use App\Values\FeatureFlagContext;
use App\Values\FeatureFlagResponse;
use Bag\Attributes\WithoutValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class FeatureFlagController
{
    public function index(Request $request): Collection
    {
        return ListFeatureFlags::run(filters: [
            'application' => $request->json('app_name'),
            'environment' => $request->json('environment'),
        ], sort: ['name' => 'desc'])->toBase()->pluck('name');
    }

    public function show(
        #[WithoutValidation]
        FeatureFlag $featureFlag,
        Request $request
    ): FeatureFlagResponse {
        try {
            $context = FeatureFlagContext::from(... $request->json()->all());

            return GetFeatureFlagStatus::run(FeatureFlag::withoutValidation(name: $featureFlag->id), $context);
        } catch (\Throwable $e) {
            $response = FeatureFlagResponse::from(
                featureFlag: $featureFlag->has('name') ? $featureFlag->name : $featureFlag->id,
                value: null,
                active: false,
            );

            if (!$featureFlag->has('name')) {
                $featureFlag = FeatureFlag::withoutValidation([
                    ...$featureFlag->toArray(),
                    'name' => $featureFlag->id,
                ]);
            }

            FeatureFlagMissedEvent::dispatch($featureFlag, $context, $response);

            return $response;
        }
    }
}
