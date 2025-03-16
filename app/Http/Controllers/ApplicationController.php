<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ApplicationService;
use App\Values\Application;
use Bag\Attributes\WithoutValidation;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationController extends Controller
{
    public function index(ApplicationService $applicationService): Response
    {
        return Inertia::render('Applications/Index', [
            'applications' => $applicationService->all(),
        ]);
    }

    public function store(Application $application, ApplicationService $applicationService)
    {
        Gate::authorize('create', $application);

        $applicationService->create($application->with(display_name: $application->display_name ?? $application->name));

        return redirect()
            ->route('applications.index')
            ->with('alert', [
                'message' => 'Application created successfully.',
                'status' => 'success',
            ]);
    }

    public function edit(
        #[WithoutValidation]
        Application $application,
        ApplicationService $applicationService
    ): Response {
        Gate::authorize('update', $application);

        return Inertia::render('Applications/Edit', [
            'application' => $applicationService->findBySlug($application->slug),
        ]);
    }

    public function update(Application $application, ApplicationService $applicationService)
    {
        Gate::authorize('update', $application);

        $applicationService->update($application);

        return redirect()
            ->route('applications.index')
            ->with('alert', [
                'message' => 'Application updated successfully.',
                'status' => 'success',
            ]);
    }
}
