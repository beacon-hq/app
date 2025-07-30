<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ApplicationService;
use App\Services\EnvironmentService;
use App\Services\FeatureFlagService;
use App\Services\FeatureTypeService;
use App\Services\MetricsService;
use App\Services\PolicyService;
use App\Services\TagService;
use App\Values\FeatureFlag;
use App\Values\FeatureFlagStatus;
use Bag\Attributes\WithoutValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
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

    public function store(
        FeatureFlag $featureFlag,
        FeatureFlagService $featureFlagService
    ): RedirectResponse {
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
        MetricsService $metricsService,
        Request $request
    ): Response {
        Gate::authorize('update', $featureFlag);

        $applicationId = $request->get('application_id');
        $environmentId = $request->get('environment_id');

        return Inertia::render('FeatureFlags/Edit', [
            'featureFlag' => $featureFlagService->find($featureFlag->id),
            'featureTypes' => $featureTypeService->all(),
            'tags' => $tagService->all(),
            'policies' => $policyService->all(),
            'applications' => $applicationService->all(),
            'environments' => $environmentService->all(),
            'log' => $featureFlagService->activityLog($featureFlag->id),
            'metrics' => $metricsService->getFlagMetrics($featureFlag->id, $applicationId, $environmentId),
        ]);
    }

    public function update(
        FeatureFlag $featureFlag,
        FeatureFlagService $featureFlagService,
    ): RedirectResponse {
        Gate::authorize('update', $featureFlag);

        $statusAppEnv = $featureFlag->statuses->map(
            fn (FeatureFlagStatus $status) =>
            $status->application->id . '-' . $status->environment->id
        );

        try {
            Validator::make(['statuses' => $statusAppEnv->toArray()], [
                'statuses.*' => 'distinct',
            ])->validate();
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withAlert('error', 'All statuses must have a unique combination of application and environment.');
        }

        $featureFlagService->update($featureFlag);

        return redirect()->back()->withAlert('success', 'Feature flag updated successfully.');
    }

}
