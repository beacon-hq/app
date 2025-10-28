<?php

declare(strict_types=1);

use App\Actions\AppContext\ClearAppContext;
use App\Actions\AppContext\GetAppContext;
use App\Actions\AppContext\SetOrganizationContext;
use App\Actions\AppContext\SetTeamContext;
use App\Models\Organization as OrganizationModel;
use App\Models\Team as TeamModel;
use App\Values\Organization;
use App\Values\Team;
use Bag\Values\Optional;

beforeEach(function () {
    // Create an organization and team using factories
    $this->organization = OrganizationModel::factory()->create([
        'name' => 'Test Organization',
    ]);

    $this->teamModel = TeamModel::factory()->for($this->organization)->create([
        'name' => 'Test Team',
        'color' => '#ff0000',
        'icon' => 'Rocket',
    ]);

    // Create an Organization value object
    $this->organizationValue = Organization::from(
        id: $this->organization->id,
        owner: null,
        name: $this->organization->name
    );

    // Create a Team value object
    $this->teamValue = Team::from(
        id: $this->teamModel->id,
        organization: $this->organizationValue,
        name: $this->teamModel->name,
        icon: $this->teamModel->icon,
        members: new Optional(),
        color: $this->teamModel->color
    );

    // Initialize the action
    $this->setTeamContextAction = SetTeamContext::make();
});

afterEach(function () {
    ClearAppContext::run();
});

it('can be instantiated using make()', function () {
    $action = SetTeamContext::make();

    expect($action)->toBeInstanceOf(SetTeamContext::class);
});

it('sets team context with Team model', function () {
    // Execute the action with a Team model
    $this->setTeamContextAction->handle($this->teamModel);

    // Get the current context
    $context = GetAppContext::run();

    // Assert that the team is correctly set in the context
    expect($context['team'])->not->toBeNull()
        ->and($context['team']->id)->toBe($this->teamModel->id)
        ->and($context['team']->name)->toBe($this->teamModel->name)
        ->and($context['team']->color)->toBe($this->teamModel->color)
        ->and($context['team']->icon)->toBe($this->teamModel->icon);
});

it('sets team context with Team value object', function () {
    // Execute the action with a Team value
    $this->setTeamContextAction->handle($this->teamValue);

    // Get the current context
    $context = GetAppContext::run();

    // Assert that the team is correctly set in the context
    expect($context['team'])->not->toBeNull()
        ->and($context['team']->id)->toBe($this->teamValue->id)
        ->and($context['team']->name)->toBe($this->teamValue->name)
        ->and($context['team']->color)->toBe($this->teamValue->color)
        ->and($context['team']->icon)->toBe($this->teamValue->icon);
});

it('can be invoked directly', function () {
    ($this->setTeamContextAction)($this->teamModel);

    $context = GetAppContext::run();

    expect($context['team'])->not->toBeNull()
        ->and($context['team']->id)->toBe($this->teamModel->id);
});

it('does not change organization context', function () {
    SetOrganizationContext::run($this->organizationValue);

    $this->setTeamContextAction->handle($this->teamModel);

    // Get the current context
    $context = GetAppContext::run();

    // Assert that the organization is still the same
    expect($context['organization'])->not->toBeNull()
        ->and($context['organization']->id)->toBe($this->organization->id)
        ->and($context['organization']->name)->toBe($this->organization->name);
});
