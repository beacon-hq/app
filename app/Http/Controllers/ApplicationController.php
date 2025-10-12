<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Applications\CreateApplication;
use App\Actions\Applications\FindApplication;
use App\Actions\Applications\ListApplications;
use App\Actions\Applications\UpdateApplication;
use App\Values\Application;
use Bag\Attributes\WithoutValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationController
{
    public function index(): Response
    {
        return Inertia::render('Applications/Index', [
            'applications' => ListApplications::run(),
        ]);
    }

    public function store(Application $application): RedirectResponse
    {
        Gate::authorize('create', $application);

        CreateApplication::run($application->with(display_name: $application->display_name ?? $application->name));

        return redirect()
            ->route('applications.index')
            ->with('alert', [
                'message' => 'Application created successfully.',
                'status' => 'success',
            ]);
    }

    public function edit(
        #[WithoutValidation]
        Application $application
    ): Response {
        Gate::authorize('update', $application);

        return Inertia::render('Applications/Edit', [
            'application' => FindApplication::run($application->id),
        ]);
    }

    public function update(Application $application): RedirectResponse
    {
        Gate::authorize('update', $application);

        UpdateApplication::run($application);

        return redirect()
            ->route('applications.index')
            ->with('alert', [
                'message' => 'Application updated successfully.',
                'status' => 'success',
            ]);
    }
}
