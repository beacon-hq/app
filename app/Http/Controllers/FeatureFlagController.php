<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ApplicationService;
use App\Services\EnvironmentService;
use App\Services\FeatureFlagService;
use App\Services\FeatureTypeService;
use App\Services\TagService;
use App\Values\FeatureFlag;
use Bag\Attributes\WithoutValidation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FeatureFlagController extends Controller
{
    public function index(FeatureFlagService $featureFlagService, FeatureTypeService $featureTypeService, TagService $tagService, Request $request)
    {
        return Inertia::render('FeatureFlags/Index', [
            'featureFlags' => $featureFlagService->all(filters: $request->get('filters', []))->toBase(),
            'featureTypes' => $featureTypeService->all(),
            'tags' => $tagService->all(),
        ]);
    }

    public function create(FeatureFlag $featureFlag, FeatureFlagService $featureFlagService)
    {
        $featureFlagService->create($featureFlag);

        return Inertia::render('FeatureFlags/Create');
    }

    public function store(FeatureFlag $featureFlag, FeatureFlagService $featureFlagService)
    {
        $featureFlagService->create($featureFlag);

        return redirect()->route('feature-flags.index')->with(
            'alert',
            [
                'message' => 'Feature flag created successfully.',
                'status' => 'success',
            ]
        );
    }

    public function edit(
        #[WithoutValidation]
        FeatureFlag $featureFlag,
        FeatureFlagService $featureFlagService,
        FeatureTypeService $featureTypeService,
        TagService $tagService,
        ApplicationService $applicationService,
        EnvironmentService $environmentService
    ) {
        return Inertia::render('FeatureFlags/Edit', [
            'featureFlag' => $featureFlagService->findBySlug($featureFlag->slug),
            'featureTypes' => $featureTypeService->all(),
            'tags' => $tagService->all(),
            'applications' => $applicationService->all(),
            'environments' => $environmentService->all(),
        ]);
    }

    public function update(
        FeatureFlag $featureFlag,
        FeatureFlagService $featureFlagService
    ) {
        $featureFlagService->update($featureFlag);

        return redirect()->route('feature-flags.edit.overview', ['slug' => $featureFlag->slug])
            ->with(
                'alert',
                [
                    'message' => 'Feature flag updated successfully.',
                    'status' => 'success',
                ]
            );
    }
}
