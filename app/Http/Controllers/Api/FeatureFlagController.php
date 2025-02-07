<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FeatureFlagService;
use App\Services\FeatureFlagStatusService;
use App\Values\Collections\FeatureFlagCollection;
use App\Values\FeatureFlag;
use App\Values\FeatureFlagContext;
use App\Values\FeatureFlagResponse;
use Bag\Attributes\WithoutValidation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class FeatureFlagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, FeatureFlagService $featureFlagService): FeatureFlagCollection
    {
        // TODO: implement per-scope filtering
        return $featureFlagService->all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(
        #[WithoutValidation]
        FeatureFlag $featureFlag,
        FeatureFlagService $featureFlagService,
        FeatureFlagStatusService $featureFlagStatusService,
        Request $request
    ) {
        $context = FeatureFlagContext::from(... $request->all());

        try {

            $featureFlag = $featureFlagService->findBySlug($featureFlag->slug);

            return $featureFlagStatusService->getStatus($featureFlag, $context);
        } catch (ModelNotFoundException $e) {
            return FeatureFlagResponse::from(
                featureFlag:  $featureFlag->slug,
                value: null,
                active: false,
            );
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FeatureFlag $featureFlag)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FeatureFlag $featureFlag)
    {
        //
    }
}
