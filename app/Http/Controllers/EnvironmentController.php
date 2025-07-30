<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\EnvironmentService;
use App\Values\Environment;
use Bag\Attributes\WithoutValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class EnvironmentController extends Controller
{
    public function index(EnvironmentService $environmentService): Response
    {
        Gate::authorize('viewAny', Environment::class);

        return Inertia::render('Environments/Index', [
            'environments' => $environmentService->all(),
        ]);
    }

    public function store(
        Environment $environment,
        EnvironmentService $environmentService
    ): RedirectResponse {
        Gate::authorize('create', $environment);

        $environmentService->create($environment);

        return redirect()
            ->route('environments.index')
            ->with('alert', [
                'message' => 'Environment created successfully.',
                'status' => 'success',
            ]);
    }

    public function edit(
        #[WithoutValidation]
        Environment $environment,
        EnvironmentService $environmentService
    ): Response {
        Gate::authorize('update', $environment);

        return Inertia::render('Environments/Edit', [
            'environment' => $environmentService->find($environment->id),
        ]);
    }

    public function update(
        Environment $environment,
        EnvironmentService $environmentService
    ): RedirectResponse {
        Gate::authorize('update', $environment);

        $environmentService->update($environment);

        return redirect()
            ->route('environments.index')
            ->withAlert('success', 'Environment updated successfully.');
    }
}
