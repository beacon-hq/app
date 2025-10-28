<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Applications\ListApplications;
use App\Actions\Environments\ListEnvironments;
use App\Actions\FeatureFlags\CountFeatureFlags;
use App\Actions\FeatureFlags\CreateFeatureFlag;
use App\Actions\FeatureFlags\FindFeatureFlag;
use App\Actions\FeatureFlags\GetFeatureFlagActivityLog;
use App\Actions\FeatureFlags\ListFeatureFlags;
use App\Actions\FeatureFlags\UpdateFeatureFlag;
use App\Actions\FeatureTypes\ListFeatureTypes;
use App\Actions\Metrics\GetFlagMetrics;
use App\Actions\Policies\ListPolicies;
use App\Actions\Tags\ListTags;
use App\Values\FeatureFlag;
use App\Values\FeatureFlagStatus;
use Bag\Attributes\WithoutValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class FeatureFlagController
{
    public function index(Request $request): Response
    {
        $sort = $request->get('sort', []);
        $filters = $request->get('filters', []);
        $page = (int) $request->get('page', 1);
        $perPage = (int) $request->get('perPage', 10);

        return Inertia::render('FeatureFlags/Index', [
            'featureFlags' => ListFeatureFlags::run(
                page: $page,
                perPage: $perPage,
                filters: $filters,
                sort: $sort
            )->toBase(),
            'featureFlagsCount' => CountFeatureFlags::run(filters: $filters),
            'page' => $page,
            'perPage' => $perPage,
            'filters' => $filters,
            'sort' => $sort,
            'featureTypes' => ListFeatureTypes::run(),
            'tags' => ListTags::run(),
            'applications' => ListApplications::run(),
            'environments' => ListEnvironments::run(),
        ]);
    }

    public function store(FeatureFlag $featureFlag): RedirectResponse
    {
        Gate::authorize('create', $featureFlag);

        $featureFlag = CreateFeatureFlag::run($featureFlag);

        return redirect()->route('feature-flags.edit', ['feature_flag' => $featureFlag->id])->with(
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
        Request $request
    ): Response {
        Gate::authorize('update', $featureFlag);

        $applicationId = $request->get('application_id');
        $environmentId = $request->get('environment_id');

        return Inertia::render('FeatureFlags/Edit', [
            'featureFlag' => FindFeatureFlag::run($featureFlag->id),
            'featureTypes' => ListFeatureTypes::run(),
            'tags' => ListTags::run(),
            'policies' => ListPolicies::run(),
            'applications' => ListApplications::run(),
            'environments' => ListEnvironments::run(),
            'log' => GetFeatureFlagActivityLog::run($featureFlag->id),
            'metrics' => Cache::flexible('metrics:' . $featureFlag->id . ':' . $applicationId . ':' . $environmentId, [60, 180], GetFlagMetrics::run($featureFlag->id, $applicationId, $environmentId)),
        ]);
    }

    public function update(FeatureFlag $featureFlag): RedirectResponse
    {
        Gate::authorize('update', $featureFlag);

        if ($featureFlag->has('statuses')) {
            $statusAppEnv = $featureFlag->statuses->map(
                fn (FeatureFlagStatus $status) => $status->application->id . '-' . $status->environment->id
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
        }

        UpdateFeatureFlag::run($featureFlag);

        return redirect()->back()->withAlert('success', 'Feature flag updated successfully.');
    }
}
