<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\AppContext\SetOrganizationContext;
use App\Actions\AppContext\SetTeamContext;
use App\Actions\Organizations\CreateOrganization;
use App\Actions\Organizations\DeleteOrganization;
use App\Actions\Organizations\FindOrganizationById;
use App\Actions\Organizations\ListOrganizations;
use App\Actions\Organizations\UpdateOrganization;
use App\Actions\Teams\CreateTeam;
use App\Values\Organization;
use App\Values\Team;
use App\Values\User;
use Bag\Attributes\WithoutValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationController
{
    public function index(): Response
    {
        return Inertia::render('Organizations/Index', [
            'organizations' => ListOrganizations::run(User::from(Auth::user())),
        ]);
    }

    public function store(Organization $organization, Request $request): RedirectResponse
    {
        Validator::make($request->json()->all(), [
            'name' => ['required', 'string', 'max:255'],
            'team.name' => ['required', 'string', 'max:255'],
            'team.color' => ['required', 'string', 'max:255'],
            'team.icon' => ['required', 'string', 'max:255'],
        ], [
            'team.name.required' => __('The team name field is required.'),
            'team.color.required' => __('The color field is required.'),
            'team.icon.required' => __('The icon field is required.'),
        ])->validate();

        $organization = CreateOrganization::run(User::from(Auth::user()), $organization);

        SetOrganizationContext::run($organization);

        $team = CreateTeam::run(Team::from(
            name: $request->json('team.name'),
            color: $request->json('team.color'),
            icon: $request->json('team.icon'),
        ), User::from(Auth::user()));

        Session::put('team', $team);
        SetTeamContext::run($team);

        return redirect()->back()
            ->withAlert('success', 'Organization Created Successfully.');
    }

    public function edit(Organization $organization): Response
    {
        return Inertia::render('Organizations/Edit', [
            'organization' => FindOrganizationById::run($organization->id),
        ]);
    }

    public function update(
        #[WithoutValidation]
        Organization $organization
    ): RedirectResponse {
        UpdateOrganization::run($organization);

        return redirect()->back()
            ->withAlert('success', 'Organization Updated Successfully.');
    }

    public function destroy(Organization $organization, Request $request): RedirectResponse
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

        DeleteOrganization::run($organization);

        return redirect()->to(route('organizations.index'))
            ->withAlert('success', 'Organization Deleted Successfully.');
    }
}
