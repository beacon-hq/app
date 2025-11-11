<?php

declare(strict_types=1);

use App\Actions\AppContext\SetAppContext;
use App\Actions\Environments\FindEnvironment;
use App\Models\Environment as EnvironmentModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;

beforeEach(function () {
    [$this->team, , $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

    $this->environment = EnvironmentModel::factory()->for($this->team)->create([
        'name' => 'test-environment',
        'description' => 'Test environment description',
        'color' => '#00ff00',
    ]);

    $this->findEnvironmentAction = FindEnvironment::make();
});

it('can find an environment by id', function () {
    $result = $this->findEnvironmentAction->handle($this->environment->id);

    expect($result->id)->toBe($this->environment->id)
        ->and($result->name)->toBe('test-environment')
        ->and($result->description)->toBe('Test environment description')
        ->and($result->color)->toBe('#00ff00');
});

it('throws an exception when environment does not exist', function () {
    $this->findEnvironmentAction->handle('non-existent-id');
})->throws(ModelNotFoundException::class);
