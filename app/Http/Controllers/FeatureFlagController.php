<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ApplicationService;
use App\Services\EnvironmentService;
use App\Services\FeatureFlagService;
use App\Services\FeatureTypeService;
use App\Services\PolicyService;
use App\Services\TagService;
use App\Values\FeatureFlag;
use Bag\Attributes\WithoutValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class FeatureFlagController extends Controller
{
    public function index(FeatureFlagService $featureFlagService, FeatureTypeService $featureTypeService, TagService $tagService, ApplicationService $applicationService, EnvironmentService $environmentService, Request $request)
    {
        debug($request->get('filters', false));
        debug($featureFlagService->count(filters: $request->get('filters', [])));

        return Inertia::render('FeatureFlags/Index', [
            'featureFlags' => $featureFlagService->all(filters: $request->get('filters', []), page: (int) $request->get('page', 1), perPage: (int) $request->get('perPage', 10))->toBase(),
            'featureFlagsCount' => $featureFlagService->count(filters: $request->get('filters', [])),
            'page' => (int) $request->get('page', 1),
            'perPage' => (int) $request->get('perPage', 10),
            'filters' => $request->get('filters', []),
            'featureTypes' => $featureTypeService->all(),
            'tags' => $tagService->all(),
            'applications' => $applicationService->all(),
            'environments' => $environmentService->all(),
        ]);
    }

    public function store(FeatureFlag $featureFlag, FeatureFlagService $featureFlagService)
    {
        Gate::authorize('create', $featureFlag);

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
        PolicyService $policyService,
        ApplicationService $applicationService,
        EnvironmentService $environmentService,
    ) {
        Gate::authorize('update', $featureFlag);

        return Inertia::render('FeatureFlags/Edit', [
            'featureFlag' => $featureFlagService->findBySlug($featureFlag->slug),
            'featureTypes' => $featureTypeService->all(),
            'tags' => $tagService->all(),
            'policies' => $policyService->all(),
            'applications' => $applicationService->all(),
            'environments' => $environmentService->all(),
        ]);
    }

    public function update(
        FeatureFlag $featureFlag,
        FeatureFlagService $featureFlagService
    ) {
        Gate::authorize('update', $featureFlag);

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
