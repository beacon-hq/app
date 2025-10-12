<?php

declare(strict_types=1);

use App\Actions\Environments\CreateEnvironment;
use App\Models\Environment as EnvironmentModel;
use App\Values\Environment;
use Tests\Unit\Actions\AppContext\SetAppContext;

beforeEach(function () {
    [$this->team, , $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

    $this->createEnvironmentAction = CreateEnvironment::make();
});

it('can create an environment', function () {
    $environment = Environment::from(
        id: '',
        name: 'new-environment',
        description: 'New environment description',
        color: '#00ff00'
    );

    $result = $this->createEnvironmentAction->handle($environment);

    // Verify the environment was created in the database
    $model = EnvironmentModel::where('name', 'new-environment')->first();

    expect($model)->not->toBeNull()
        ->and($model->name)->toBe('new-environment')
        ->and($model->description)->toBe('New environment description')
        ->and($model->team_id)->toBe($this->team->id)
        ->and($result->name)->toBe('new-environment')
        ->and($result->description)->toBe('New environment description')
        ->and($result->id)->toBe($model->id);
});
