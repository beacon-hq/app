<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App;
use App\Services\OrganizationService;
use App\Services\TeamService;
use App\Values\Organization;
use App\Values\User;
use Bag\Attributes\WithoutValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;
use Session;

class OrganizationController extends Controller
{
    public function __construct(protected OrganizationService $organizationService, protected TeamService $teamService)
    {
    }

    public function index(): Response
    {
        return Inertia::render('Organizations/Index');
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

    public function edit(Organization $organization): Response
    {
        return Inertia::render('Organizations/Edit', [
            'organization' => $this->organizationService->findById($organization->id),
        ]);
    }

    public function update(
        #[WithoutValidation]
        Organization $organization
    ): RedirectResponse {
        $organization = $this->organizationService->update($organization);

        return redirect()->to(route('organizations.edit', ['id' => $organization->id]))
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

        $this->organizationService->delete($organization);

        return redirect()->to(route('organizations.index'))
            ->withAlert('success', 'Organization Deleted Successfully.');
    }
}
