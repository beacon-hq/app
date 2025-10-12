<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\FeatureTypes\CreateFeatureType;
use App\Actions\FeatureTypes\FindFeatureType;
use App\Actions\FeatureTypes\ListFeatureTypes;
use App\Actions\FeatureTypes\SetDefaultFeatureType;
use App\Actions\FeatureTypes\UpdateFeatureType;
use App\Values\FeatureType;
use Bag\Attributes\WithoutValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class FeatureTypeController
{
    public function index(): Response
    {
        return Inertia::render('FeatureTypes/Index', [
            'featureTypes' => ListFeatureTypes::run(),
        ]);
    }

    public function store(FeatureType $featureType): RedirectResponse
    {
        Gate::authorize('create', $featureType);

        $newFeatureType = CreateFeatureType::run($featureType);

        if ($featureType->isDefault) {
            SetDefaultFeatureType::run($newFeatureType->id);
        }

        return redirect()->route('feature-types.index')->with(
            'alert',
            [
                'message' => 'Feature type created successfully.',
                'status' => 'success',
            ]
        );
    }

    public function edit(
        #[WithoutValidation]
        FeatureType $featureType
    ): Response {
        Gate::authorize('update', $featureType);

        return Inertia::render('FeatureTypes/Edit', [
            'featureType' => FindFeatureType::run($featureType->id),
        ]);
    }

    public function update(FeatureType $featureType): RedirectResponse
    {
        Gate::authorize('update', $featureType);

        UpdateFeatureType::run($featureType);

        if ($featureType->isDefault) {
            SetDefaultFeatureType::run($featureType->id);
        }

        return redirect()->route('feature-types.index')->with(
            'alert',
            [
                'message' => 'Feature type updated successfully.',
                'status' => 'success',
            ]
        );
    }

    public function setDefault(
        #[WithoutValidation]
        FeatureType $featureType
    ): RedirectResponse {
        Gate::authorize('update', $featureType);

        SetDefaultFeatureType::run($featureType->id);

        return redirect()->route('feature-types.index')->with(
            'alert',
            [
                'message' => 'Default feature type updated successfully.',
                'status' => 'success',
            ]
        );
    }
}
