<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\PolicyService;
use App\Values\Policy;
use Bag\Attributes\WithoutValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PolicyController extends Controller
{
    public function index(PolicyService $policyService): Response
    {
        $policies = $policyService->all();

        return Inertia::render('Policies/Index', [
            'policies' => $policies,
        ]);
    }

    public function store(Policy $policy, PolicyService $policyService): RedirectResponse
    {
        Gate::authorize('create', $policy);

        $policyService->create($policy);

        return redirect()->route('policies.index')->with(
            'alert',
            [
                'message' => 'Policy created successfully.',
                'status' => 'success',
            ]
        );
    }

    public function edit(
        #[WithoutValidation]
        Policy $policy,
        PolicyService $policyService
    ): Response {
        Gate::authorize('update', $policy);

        return Inertia::render('Policies/Edit', [
            'policy' => $policyService->find($policy->id),
            'policies' => $policyService->all(),
        ]);
    }

    public function update(Policy $policy, PolicyService $policyService): RedirectResponse
    {
        Gate::authorize('update', $policy);

        $policyService->update($policy);

        return redirect()->route('policies.edit', ['policy' => $policy->id])->with(
            'alert',
            [
                'message' => 'Policy updated successfully.',
                'status' => 'success',
            ]
        );
    }
}
