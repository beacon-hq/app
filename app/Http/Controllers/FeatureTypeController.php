<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\FeatureTypeService;
use App\Values\FeatureType;
use Bag\Attributes\WithoutValidation;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class FeatureTypeController extends Controller
{
    public function index(FeatureTypeService $featureTypeService)
    {
        return Inertia::render('FeatureTypes/Index', [
            'featureTypes' => $featureTypeService->all(),
        ]);
    }

    public function store(FeatureType $featureType, FeatureTypeService $featureTypeService)
    {
        Gate::authorize('create', $featureType);

        $featureTypeService->create($featureType);

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
        FeatureType $featureType,
        FeatureTypeService $featureTypeService
    ) {
        Gate::authorize('update', $featureType);

        return Inertia::render('FeatureTypes/Edit', [
            'featureType' => $featureTypeService->findBySlug($featureType->slug),
        ]);
    }

    public function update(FeatureType $featureType, FeatureTypeService $featureTypeService)
    {
        Gate::authorize('update', $featureType);

        $featureTypeService->update($featureType);

        return redirect()->route('feature-types.index')->with(
            'alert',
            [
                'message' => 'Feature type updated successfully.',
                'status' => 'success',
            ]
        );
    }
}
