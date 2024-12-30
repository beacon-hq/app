<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\FeatureFlagRequest;
use App\Models\FeatureFlag;
use App\Models\FeatureType;
use App\Models\Tag;
use Inertia\Inertia;

class FeatureFlagController extends Controller
{
    public function index()
    {
        $featureFlags = FeatureFlag::with('featureType')->orderBy('name')->get();
        $featureTypes = fn () => FeatureType::orderBy('name')->get();

        $tags = fn () => Tag::orderBy('name')->get();

        return Inertia::render('FeatureFlags/Index', [
            'featureFlags' => $featureFlags,
            'featureTypes' => $featureTypes,
            'tags' => $tags,
        ]);
    }

    public function create()
    {
        return Inertia::render('FeatureFlags/Create');
    }

    public function store(FeatureFlagRequest $request)
    {
        FeatureFlag::create($request->validated());

        return redirect()->route('feature-flags.index')->with(
            'alert',
            [
                'message' => 'Feature flag created successfully.',
                'status' => 'success',
            ]
        );
    }

    public function edit(FeatureFlag $featureFlag)
    {
        return Inertia::render('FeatureFlags/Edit', [
            'featureFlag' => $featureFlag,
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    public function update(FeatureFlagRequest $request, FeatureFlag $featureFlag)
    {
        $featureFlag->update($request->only('name', 'description', 'feature_type_id'));

        return redirect()->route('feature-flags.index')
            ->with(
                'alert',
                [
                    'message' => 'Feature flag updated successfully.',
                    'status' => 'success',
                ]
            );
    }
}
