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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class FeatureFlagController extends Controller
{
    public function index(
        FeatureFlagService $featureFlagService,
        FeatureTypeService $featureTypeService,
        TagService $tagService,
        ApplicationService $applicationService,
        EnvironmentService $environmentService,
        Request $request
    ): Response {
        $sort = $request->get('sort', []);

        return Inertia::render('FeatureFlags/Index', [
            'featureFlags' => $featureFlagService->all(
                filters: $request->get('filters', []),
                page: (int) $request->get('page', 1),
                perPage: (int) $request->get('perPage', 10),
                sort: $sort
            )->toBase(),
            'featureFlagsCount' => $featureFlagService->count(filters: $request->get('filters', [])),
            'page' => (int) $request->get('page', 1),
            'perPage' => (int) $request->get('perPage', 10),
            'filters' => $request->get('filters', []),
            'sort' => $sort,
            'featureTypes' => $featureTypeService->all(),
            'tags' => $tagService->all(),
            'applications' => $applicationService->all(),
            'environments' => $environmentService->all(),
        ]);
    }

    public function store(FeatureFlag $featureFlag, FeatureFlagService $featureFlagService): RedirectResponse
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
    ): Response {
        Gate::authorize('update', $featureFlag);

        return Inertia::render('FeatureFlags/Edit', [
            'featureFlag' => $featureFlagService->find($featureFlag->id),
            'featureTypes' => $featureTypeService->all(),
            'tags' => $tagService->all(),
            'policies' => $policyService->all(),
            'applications' => $applicationService->all(),
            'environments' => $environmentService->all(),
            'log' => $featureFlagService->activityLog($featureFlag->id),
        ]);
    }

    public function update(
        FeatureFlag $featureFlag,
        FeatureFlagService $featureFlagService
    ): RedirectResponse {
        Gate::authorize('update', $featureFlag);

        $featureFlagService->update($featureFlag);

        return redirect()->route('feature-flags.edit.overview', ['feature_flag' => $featureFlag->id])
            ->with(
                'alert',
                [
                    'message' => 'Feature flag updated successfully.',
                    'status' => 'success',
                ]
            );
    }
}
