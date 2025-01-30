<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\EnvironmentService;
use App\Values\Environment;
use Bag\Attributes\WithoutValidation;
use Inertia\Inertia;
use Inertia\Response;

class EnvironmentController extends Controller
{
    public function index(EnvironmentService $environmentService): Response
    {
        return Inertia::render('Environments/Index', [
            'environments' => $environmentService->all(),
        ]);
    }

    public function store(Environment $environment, EnvironmentService $environmentService)
    {
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
        return Inertia::render('Environments/Edit', [
            'environment' => $environmentService->findBySlug($environment->slug),
        ]);
    }

    public function update(Environment $environment, EnvironmentService $environmentService)
    {
        $environmentService->update($environment);

        return redirect()
            ->route('environments.index')
            ->with('alert', [
                'message' => 'Environment updated successfully.',
                'status' => 'success',
            ]);
    }
}
