<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Environments\CreateEnvironment;
use App\Actions\Environments\FindEnvironment;
use App\Actions\Environments\ListEnvironments;
use App\Actions\Environments\UpdateEnvironment;
use App\Values\Environment;
use Bag\Attributes\WithoutValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class EnvironmentController
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Environment::class);

        return Inertia::render('Environments/Index', [
            'environments' => ListEnvironments::run(),
        ]);
    }

    public function store(Environment $environment): RedirectResponse
    {
        Gate::authorize('create', $environment);

        CreateEnvironment::run($environment);

        return redirect()
            ->route('environments.index')
            ->with('alert', [
                'message' => 'Environment created successfully.',
                'status' => 'success',
            ]);
    }

    public function edit(
        #[WithoutValidation]
        Environment $environment
    ): Response {
        Gate::authorize('update', $environment);

        return Inertia::render('Environments/Edit', [
            'environment' => FindEnvironment::run($environment->id),
        ]);
    }

    public function update(Environment $environment): RedirectResponse
    {
        Gate::authorize('update', $environment);

        UpdateEnvironment::run($environment);

        return redirect()
            ->route('environments.index')
            ->withAlert('success', 'Environment updated successfully.');
    }
}
