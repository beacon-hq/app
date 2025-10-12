<?php

declare(strict_types=1);

use App\Actions\Environments\ListEnvironments;
use App\Models\Environment as EnvironmentModel;
use App\Values\Collections\EnvironmentCollection;
use App\Values\Environment;
use Illuminate\Support\Facades\DB;
use Tests\Unit\Actions\AppContext\SetAppContext;

beforeEach(function () {
    DB::beginTransaction();

    [$this->team, , $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

    // Create multiple environments for testing
    $this->environment1 = EnvironmentModel::factory()->for($this->team)->create([
        'name' => 'production',
        'description' => 'Production environment',
        'color' => '#ff0000',
    ]);

    $this->environment2 = EnvironmentModel::factory()->for($this->team)->create([
        'name' => 'staging',
        'description' => 'Staging environment',
        'color' => '#00ff00',
    ]);

    $this->environment3 = EnvironmentModel::factory()->for($this->team)->create([
        'name' => 'development',
        'description' => 'Development environment',
        'color' => '#0000ff',
    ]);

    $this->listEnvironmentsAction = ListEnvironments::make();
});

afterEach(function () {
    DB::rollBack();
});

it('retrieves all environments ordered by name', function () {
    $result = $this->listEnvironmentsAction->handle();

    expect($result)->toBeInstanceOf(EnvironmentCollection::class)
        ->and($result)->toHaveCount(3)
        ->and($result[0]->name)->toBe('development')
        ->and($result[1]->name)->toBe('production')
        ->and($result[2]->name)->toBe('staging');
});

it('returns environment values with correct properties', function () {
    $result = $this->listEnvironmentsAction->handle();

    $firstEnvironment = $result[0];

    expect($firstEnvironment)->toBeInstanceOf(Environment::class)
        ->and($firstEnvironment->id)->toBe($this->environment3->id)
        ->and($firstEnvironment->name)->toBe('development')
        ->and($firstEnvironment->description)->toBe('Development environment')
        ->and($firstEnvironment->color)->toBe('#0000ff');
});

it('returns an empty collection when no environments exist', function () {
    // Delete all environments
    EnvironmentModel::query()->delete();

    $result = $this->listEnvironmentsAction->handle();

    expect($result)->toBeInstanceOf(EnvironmentCollection::class)
        ->and($result)->toBeEmpty();
});
