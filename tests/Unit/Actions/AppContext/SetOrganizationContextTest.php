<?php

declare(strict_types=1);

use App\Actions\AppContext\GetAppContext;
use App\Actions\AppContext\SetOrganizationContext;
use App\Models\Organization as OrganizationModel;
use App\Models\Team;
use App\Models\User as UserModel;
use App\Values\Organization;
use Bag\Values\Optional;
use Tests\Unit\Actions\AppContext\ClearAppContext;
use Tests\Unit\Actions\AppContext\SetAppContext;

beforeEach(function () {
    $this->user = UserModel::factory()->create();
    $this->organization = OrganizationModel::factory()->create([
        'name' => 'Test Organization',
    ]);

    $this->setOrganizationContextAction = SetOrganizationContext::make();
    $this->getAppContextAction = GetAppContext::make();
});

afterEach(function () {
    ClearAppContext::run();
});

it('sets organization context using organization model', function () {
    // Call the action with the model
    $this->setOrganizationContextAction->handle($this->organization);

    // Get the context to verify
    $appContext = $this->getAppContextAction->handle();

    // Verify the context has the expected organization
    expect($appContext['organization']->id)->toBe($this->organization->id)
        ->and($appContext['organization']->name)->toBe('Test Organization')
        ->and($appContext['team'])->toBeNull();
});

it('sets organization context using organization value object', function () {
    // Create organization value object
    $organizationValue = Organization::from(
        id: $this->organization->id,
        owner: null,
        name: $this->organization->name
    );

    // Call the action with the value object
    $this->setOrganizationContextAction->handle($organizationValue);

    // Get the context to verify
    $appContext = $this->getAppContextAction->handle();

    // Verify the context has the expected organization
    expect($appContext['organization']->id)->toBe($this->organization->id)
        ->and($appContext['organization']->name)->toBe('Test Organization')
        ->and($appContext['team'])->toBeNull();
});

it('preserves existing team context when setting organization', function () {
    // Set up a team in the context first
    $organization = Organization::from(
        id: $this->organization->id,
        owner: null,
        name: $this->organization->name
    );

    // Create a team for testing
    $team = Team::factory()->for($this->organization)->create([
        'name' => 'Test Team',
    ]);

    // Convert to value object
    $teamValue = \App\Values\Team::from(
        id: $team->id,
        organization: $organization,
        name: $team->name,
        icon: $team->icon,
        members: new Optional(),
        color: $team->color
    );

    SetAppContext::run(null, $teamValue);

    $this->setOrganizationContextAction->handle($this->organization);

    $appContext = $this->getAppContextAction->handle();

    expect($appContext['organization']->id)->toBe($this->organization->id)
        ->and($appContext['organization']->name)->toBe('Test Organization')
        ->and($appContext['team']->id)->toBe($team->id)
        ->and($appContext['team']->name)->toBe('Test Team');
});
