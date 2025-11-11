<?php

declare(strict_types=1);

use App\Actions\AppContext\SetAppContext;
use App\Actions\Applications\CreateApplication;
use App\Models\Application as ApplicationModel;
use App\Values\Application;

beforeEach(function () {
    [$this->team, $user, $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

    $this->createApplicationAction = CreateApplication::make();
});

it('can create an application', function () {
    $application = Application::from(
        id: '',
        name: 'new-app',
        displayName: 'New App',
        description: 'New application description',
        environments: null,
        color: '#00ff00'
    );

    $result = $this->createApplicationAction->handle($application);

    // Verify the application was created in the database
    $model = ApplicationModel::where('name', 'new-app')->first();

    expect($model)->not->toBeNull()
        ->and($model->name)->toBe('new-app')
        ->and($model->description)->toBe('New application description')
        ->and($model->team_id)->toBe($this->team->id)
        ->and($result->name)->toBe('new-app')
        ->and($result->description)->toBe('New application description')
        ->and($result->id)->toBe($model->id);
});
