<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App;
use App\Services\OrganizationService;
use App\Services\TeamService;
use App\Values\Organization;
use App\Values\User;
use Bag\Attributes\WithoutValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Session;

class OrganizationController extends Controller
{
    public function __construct(protected OrganizationService $organizationService, protected TeamService $teamService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Organizations/Index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Organization $organization, Request $request)
    {
        $organization = $this->organizationService->create(User::from(Auth::user()), $organization);

        App::context(organization: $organization);

        $team = $this->teamService->create(App\Values\Team::from(
            name: $request->json('team.name'),
            color: $request->json('team.color'),
            icon: $request->json('team.icon'),
        ), User::from(Auth::user()));

        Session::put('team', $team);
        App::context(team: $team);

        return redirect()->to(route('organizations.edit', ['id' => $organization->id]))
            ->withAlert('success', 'Organization Created Successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Organization $organization)
    {
        return Inertia::render('Organizations/Edit', [
            'organization' => $this->organizationService->findById($organization->id),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    #[WithoutValidation]
    public function update(Organization $organization)
    {
        $organization = $this->organizationService->update($organization);

        return redirect()->to(route('organizations.edit', ['id' => $organization->id]))
            ->withAlert('success', 'Organization Updated Successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Organization $organization, Request $request)
    {
        if ($request->json('password') === null) {
            return redirect()->back()
                ->withAlert('error', 'Password is required to delete organization.');
        }

        Validator::make($request->json()->all(), [
            'password' => ['required', 'string', 'current_password:web'],
        ], [
            'current_password.current_password' => __('The provided password does not match your current password.'),
        ])->validate();

        $this->organizationService->delete($organization);

        return redirect()->to(route('organizations.index'))
            ->withAlert('success', 'Organization Deleted Successfully.');
    }
}
