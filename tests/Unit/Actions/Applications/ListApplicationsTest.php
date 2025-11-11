<?php

declare(strict_types=1);

use App\Actions\AppContext\SetAppContext;
use App\Actions\Applications\ListApplications;
use App\Models\Application as ApplicationModel;

beforeEach(function () {
    [$this->team, , $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

    // Create applications with names in non-alphabetical order
    $this->applicationC = ApplicationModel::factory()->for($this->team)->create([
        'name' => 'c-application',
        'display_name' => 'C Application',
        'description' => 'Description for C',
    ]);

    $this->applicationA = ApplicationModel::factory()->for($this->team)->create([
        'name' => 'a-application',
        'display_name' => 'A Application',
        'description' => 'Description for A',
    ]);

    $this->applicationB = ApplicationModel::factory()->for($this->team)->create([
        'name' => 'b-application',
        'display_name' => 'B Application',
        'description' => 'Description for B',
    ]);

    $this->listApplicationsAction = ListApplications::make();
});

it('returns all applications sorted by name', function () {
    $result = $this->listApplicationsAction->handle();

    expect($result)->toHaveCount(3)
        ->and($result[0]->name)->toBe('a-application')
        ->and($result[1]->name)->toBe('b-application')
        ->and($result[2]->name)->toBe('c-application');
});

it('returns an empty collection when no applications exist', function () {
    // Delete all applications
    ApplicationModel::query()->delete();

    $result = $this->listApplicationsAction->handle();

    expect($result)->toHaveCount(0)
        ->and($result)->toBeEmpty();
});

it('includes all application properties in the returned collection', function () {
    $result = $this->listApplicationsAction->handle();

    // Find the first application in the result
    $appA = $result->first(fn ($app) => $app->name === 'a-application');

    expect($appA)->not->toBeNull()
        ->and($appA->id)->toBe($this->applicationA->id)
        ->and($appA->displayName)->toBe('A Application')
        ->and($appA->description)->toBe('Description for A');
});
