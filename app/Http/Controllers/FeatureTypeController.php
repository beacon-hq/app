<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\FeatureFlagRequest;
use App\Http\Requests\FeatureTypeRequest;
use App\Models\FeatureType;
use Inertia\Inertia;

class FeatureTypeController extends Controller
{
    public function index()
    {
        $featureTypes = FeatureType::orderBy('name')->get();

        return Inertia::render('FeatureTypes/Index', [
            'featureTypes' => $featureTypes,
        ]);
    }

    public function store(FeatureFlagRequest $request)
    {
        FeatureType::create($request->validated());

        return redirect()->route('feature-types.index')->with(
            'alert',
            [
                'message' => 'Feature type created successfully.',
                'status' => 'success',
            ]
        );
    }

    public function edit(FeatureType $featureType)
    {
        return Inertia::render('FeatureTypes/Edit', [
            'featureType' => $featureType,
        ]);
    }

    public function update(FeatureTypeRequest $request, FeatureType $featureType)
    {
        $featureType->update($request->all());

        return redirect()->route('feature-types.index')->with(
            'alert',
            [
                'message' => 'Feature type updated successfully.',
                'status' => 'success',
            ]
        );
    }
}
