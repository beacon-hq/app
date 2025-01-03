<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ApplicationRequest;
use App\Models\Application;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationController extends Controller
{
    /**
     * Display a listing of the applications.
     */
    public function index(): Response
    {
        $applications = Application::query()
            ->orderBy('display_name')
            ->orderBy('name')
            ->get()->append('environments');

        return Inertia::render('Applications/Index', [
            'applications' => $applications,
        ]);
    }

    /**
     * Show the form for creating a new application.
     */
    public function create(): Response
    {
        return Inertia::render('Applications/Create');
    }

    /**
     * Store a newly created application in storage.
     */
    public function store(ApplicationRequest $request)
    {
        Application::create([
            ... $request->safe()->except('color'),
            'color' => $request->validated('color', ''),
        ]);

        return redirect()
            ->route('applications.index')
            ->with('alert', [
                'message' => 'Application created.',
                'status' => 'success',
            ]);
    }

    /**
     * Show the form for editing the specified application.
     */
    public function edit(Application $application): Response
    {
        return Inertia::render('Applications/Edit', [
            'application' => $application,
        ]);
    }

    /**
     * Update the specified application in storage.
     */
    public function update(ApplicationRequest $request, Application $application)
    {
        $application->update([
            ... $request->safe()->except('color'),
            'color' => $request->validated('color', ''),
        ]);

        return redirect()
            ->route('applications.index')
            ->with('alert', [
                'message' => 'Application updated.',
                'status' => 'success',
            ]);
    }
}
