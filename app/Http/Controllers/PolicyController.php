<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\PolicyRequest;
use App\Models\Policy;
use Inertia\Inertia;

class PolicyController extends Controller
{
    public function index()
    {
        $policies = Policy::orderBy('name')->get();

        return Inertia::render('Policies/Index', [
            'policies' => $policies,
        ]);
    }

    public function store(PolicyRequest $request)
    {
        Policy::create([...$request->validated(), 'definition' => []]);

        return redirect()->route('policies.index')->with(
            'alert',
            [
                'message' => 'Policy created successfully.',
                'status' => 'success',
            ]
        );
    }

    public function edit(Policy $policy)
    {
        return Inertia::render('Policies/Edit', [
            'policy' => $policy,
            'policies' => Policy::orderBy('name')->where('id', '!=', $policy->id)->get(),
        ]);
    }

    public function update(PolicyRequest $request, Policy $policy)
    {
        $policy->update($request->validated());

        return redirect()->back()->with(
            'alert',
            [
                'message' => 'Policy updated successfully.',
                'status' => 'success',
            ]
        );
    }
}
