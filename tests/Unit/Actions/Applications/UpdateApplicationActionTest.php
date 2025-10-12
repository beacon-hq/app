<?php

declare(strict_types=1);

use App\Actions\Applications\UpdateApplication;
use App\Models\Application as ApplicationModel;
use App\Values\Application;
use Bag\Values\Optional;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\Unit\Actions\AppContext\SetAppContext;

beforeEach(function () {
    [$this->team, , $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

    $this->application = ApplicationModel::factory()->for($this->team)->create([
        'name' => 'original-name',
        'display_name' => 'Original Name',
        'description' => 'Original description',
        'color' => '#ff0000',
    ]);

    $this->updateApplicationAction = UpdateApplication::make();
});

it('can update an application', function () {
    $application = Application::from(
        id: $this->application->id,
        name: 'updated-name',
        displayName: 'Updated Name',
        description: 'Updated description',
        environments: new Optional(),
        color: '#00ff00'
    );

    $result = $this->updateApplicationAction->handle($application);

    $this->application->refresh();

    expect($this->application->name)->toBe('updated-name')
        ->and($this->application->description)->toBe('Updated description')
        ->and($result->name)->toBe('updated-name')
        ->and($result->description)->toBe('Updated description')
        ->and($result->id)->toBe($this->application->id);
});

it('throws an exception when application does not exist', function () {
    $application = Application::from(
        id: 'non-existent-id',
        name: 'updated-name',
        displayName: 'Updated Name',
        description: 'Updated description',
        environments: new Optional(),
        color: '#00ff00'
    );

    $this->updateApplicationAction->handle($application);
})->throws(ModelNotFoundException::class);
