<?php

declare(strict_types=1);

use App\Actions\Environments\FindEnvironmentByName;
use App\Models\Environment as EnvironmentModel;
use Tests\Unit\Actions\AppContext\SetAppContext;

beforeEach(function () {
    [$this->team, , $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

    $this->environment = EnvironmentModel::factory()->for($this->team)->create([
        'name' => 'test-environment',
        'description' => 'Test environment description',
        'color' => '#00ff00',
    ]);

    $this->findEnvironmentByNameAction = FindEnvironmentByName::make();
});

it('can find an environment by name', function () {
    $result = $this->findEnvironmentByNameAction->handle('test-environment');

    expect($result)->not->toBeNull()
        ->and($result->id)->toBe($this->environment->id)
        ->and($result->name)->toBe('test-environment')
        ->and($result->description)->toBe('Test environment description')
        ->and($result->color)->toBe('#00ff00');
});

it('returns null when environment does not exist', function () {
    $result = $this->findEnvironmentByNameAction->handle('non-existent-environment');

    expect($result)->toBeNull();
});
