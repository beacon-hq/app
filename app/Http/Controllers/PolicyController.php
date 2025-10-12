<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Policies\CreatePolicy;
use App\Actions\Policies\FindPolicy;
use App\Actions\Policies\ListPolicies;
use App\Actions\Policies\UpdatePolicy;
use App\Values\Policy;
use Bag\Attributes\WithoutValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PolicyController
{
    public function index(): Response
    {
        return Inertia::render('Policies/Index', [
            'policies' => ListPolicies::run(),
        ]);
    }

    public function store(Policy $policy): RedirectResponse
    {
        Gate::authorize('create', $policy);

        $newPolicy = CreatePolicy::run($policy);

        return redirect()
            ->route('policies.edit', ['policy' => $newPolicy->id])
            ->withAlert(
                'success',
                'Policy created successfully.',
            );
    }

    public function edit(
        #[WithoutValidation]
        Policy $policy
    ): Response {
        Gate::authorize('update', $policy);

        return Inertia::render('Policies/Edit', [
            'policy' => FindPolicy::run($policy->id),
            'policies' => ListPolicies::run(),
        ]);
    }

    public function update(Policy $policy): RedirectResponse
    {
        Gate::authorize('update', $policy);

        UpdatePolicy::run($policy);

        return redirect()->route('policies.edit', ['policy' => $policy->id])->with(
            'alert',
            [
                'message' => 'Policy updated successfully.',
                'status' => 'success',
            ]
        );
    }
}
