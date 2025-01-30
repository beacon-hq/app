<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FeatureFlagService;
use App\Values\Collections\FeatureFlagCollection;
use App\Values\FeatureFlag;
use Bag\Attributes\WithoutValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Lottery;

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
        FeatureFlagService $featureFlagService
    ) {
        // TODO: Use the policies to check if the flag is active
        // $featureFlag = FeatureFlag::where('slug', $featureFlag)->firstOrFail();
        return ['active' => Lottery::odds(1, 2)->choose(), 'value' => $featureFlagService->findBySlug($featureFlag->slug)->name];
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
