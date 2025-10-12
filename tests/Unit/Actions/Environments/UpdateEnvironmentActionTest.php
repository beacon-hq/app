<?php

declare(strict_types=1);

use App\Actions\Environments\UpdateEnvironment;
use App\Models\Environment as EnvironmentModel;
use App\Values\Environment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\Unit\Actions\AppContext\SetAppContext;

beforeEach(function () {
    [$this->team, , $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

    $this->environment = EnvironmentModel::factory()->for($this->team)->create([
        'name' => 'original-environment',
        'description' => 'Original environment description',
        'color' => '#ff0000',
    ]);

    $this->updateEnvironmentAction = UpdateEnvironment::make();
});

it('can update an environment', function () {
    $environment = Environment::from(
        id: $this->environment->id,
        name: 'updated-environment',
        description: 'Updated environment description',
        color: '#00ff00'
    );

    $result = $this->updateEnvironmentAction->handle($environment);

    $this->environment->refresh();

    expect($this->environment->name)->toBe('updated-environment')
        ->and($this->environment->description)->toBe('Updated environment description')
        ->and($result->name)->toBe('updated-environment')
        ->and($result->description)->toBe('Updated environment description')
        ->and($result->id)->toBe($this->environment->id);
});

it('throws an exception when environment does not exist', function () {
    $environment = Environment::from(
        id: 'non-existent-id',
        name: 'updated-environment',
        description: 'Updated environment description',
        color: '#00ff00'
    );

    $this->updateEnvironmentAction->handle($environment);
})->throws(ModelNotFoundException::class);
