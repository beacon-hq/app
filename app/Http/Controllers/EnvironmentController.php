<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\EnvironmentRequest;
use App\Models\Environment;
use Inertia\Inertia;
use Inertia\Response;

class EnvironmentController extends Controller
{
    /**
     * Display a listing of the applications.
     */
    public function index(): Response
    {
        $applications = Environment::query()
            ->orderBy('name')
            ->get();

        return Inertia::render('Environments/Index', [
            'environments' => $applications,
        ]);
    }

    /**
     * Store a newly created application in storage.
     */
    public function store(EnvironmentRequest $request)
    {
        Environment::create([
            ... $request->safe()->except('color'),
            'color' => $request->validated('color', ''),
        ]);

        return redirect()
            ->route('environments.index')
            ->with('alert', [
                'message' => 'Environment created.',
                'status' => 'success',
            ]);
    }

    /**
     * Show the form for editing the specified application.
     */
    public function edit(Environment $environment): Response
    {
        return Inertia::render('Environments/Edit', [
            'environment' => $environment,
        ]);
    }

    /**
     * Update the specified application in storage.
     */
    public function update(EnvironmentRequest $request, Environment $environment)
    {
        $environment->update([
            ... $request->safe()->except('color'),
            'color' => $request->validated('color', ''),
        ]);

        return redirect()
            ->route('environments.index')
            ->with('alert', [
                'message' => 'Environment updated.',
                'status' => 'success',
            ]);
    }
}
