<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\TeamRepository;
use App\Values\Team;
use Bag\Attributes\WithoutValidation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeamsController extends Controller
{
    public function __construct(protected TeamRepository $teamRepository)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Teams/Index', [
            'teams' => $this->teamRepository->all(null, true),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(
        #[WithoutValidation]
        Team $team
    ) {
        return Inertia::render('Teams/Edit', [
            'team' => $this->teamRepository->findBySlug($team->slug, true),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
