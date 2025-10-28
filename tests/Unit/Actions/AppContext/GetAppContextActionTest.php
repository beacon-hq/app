<?php

declare(strict_types=1);

use App\Actions\AppContext\ClearAppContext;
use App\Actions\AppContext\GetAppContext;
use App\Actions\AppContext\SetAppContext;
use App\Models\Organization as OrganizationModel;
use App\Models\Team as TeamModel;
use App\Models\User as UserModel;
use App\Values\Organization;
use App\Values\Team;
use Bag\Values\Optional;

beforeEach(function () {
    $this->user = UserModel::factory()->create();
    $this->organization = OrganizationModel::factory()->create([
        'name' => 'Test Organization',
    ]);
    $this->team = TeamModel::factory()->for($this->organization)->createQuietly([
        'name' => 'Test Team',
        'color' => '#ff0000',
        'icon' => 'Rocket',
    ]);

    $this->getAppContextAction = GetAppContext::make();
});

afterEach(function () {
    ClearAppContext::run();
});

it('returns the current app context', function () {
    // Set up app context with organization and team
    $organization = Organization::from(
        id: $this->organization->id,
        owner: null,
        name: $this->organization->name
    );

    $team = Team::from(
        id: $this->team->id,
        organization: $organization,
        name: $this->team->name,
        icon: $this->team->icon,
        members: new Optional(),
        color: $this->team->color
    );

    SetAppContext::run($organization, $team);

    // Call the action
    $result = $this->getAppContextAction->handle();

    // Verify the context has the expected values
    expect($result['organization']->id)->toBe($this->organization->id)
        ->and($result['organization']->name)->toBe('Test Organization')
        ->and($result['team']->id)->toBe($this->team->id)
        ->and($result['team']->name)->toBe('Test Team')
        ->and($result['team']->color)->toBe('#ff0000')
        ->and($result['team']->icon)->toBe('Rocket');
});

it('returns empty context when not set', function () {
    ClearAppContext::run();

    $result = $this->getAppContextAction->handle();

    expect($result)->toBe(['organization' => null, 'team' => null]);
});
