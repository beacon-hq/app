<?php

declare(strict_types=1);

use App\Actions\AppContext\SetAppContext;
use App\Actions\Environments\GetAllEnvironments;
use App\Models\Environment as EnvironmentModel;
use App\Values\Collections\EnvironmentCollection;
use Illuminate\Support\Facades\DB;

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

    $this->getAllEnvironmentsAction = GetAllEnvironments::make();
});

afterEach(function () {
    DB::rollBack();
});

it('retrieves all environments ordered by name by default', function () {
    $result = $this->getAllEnvironmentsAction->handle();

    expect($result)->toBeInstanceOf(EnvironmentCollection::class)
        ->and($result)->toHaveCount(3)
        ->and($result[0]->name)->toBe('development')
        ->and($result[1]->name)->toBe('production')
        ->and($result[2]->name)->toBe('staging');
});

it('retrieves all environments ordered by specified column', function () {
    // Order by description
    $result = $this->getAllEnvironmentsAction->handle('description');

    expect($result)->toBeInstanceOf(EnvironmentCollection::class)
        ->and($result)->toHaveCount(3)
        ->and($result[0]->name)->toBe('development')
        ->and($result[1]->name)->toBe('production')
        ->and($result[2]->name)->toBe('staging');
});

it('retrieves all environments ordered by multiple columns', function () {
    // Create another environment for testing multiple column ordering
    $this->environment4 = EnvironmentModel::factory()->for($this->team)->create([
        'name' => 'production-clone',
        'description' => 'Another production environment',
        'color' => '#ff00ff',
    ]);

    // Order by name, then by description
    $result = $this->getAllEnvironmentsAction->handle(['name', 'description']);

    expect($result)->toBeInstanceOf(EnvironmentCollection::class)
        ->and($result)->toHaveCount(4)
        // First should be development
        ->and($result[0]->name)->toBe('development')
        // Next should be production and production-clone, ordered by name
        ->and($result[1]->name)->toBe('production')
        ->and($result[1]->description)->toBe('Production environment')
        ->and($result[2]->name)->toBe('production-clone')
        ->and($result[2]->description)->toBe('Another production environment')
        // Last should be staging
        ->and($result[3]->name)->toBe('staging');
});

it('returns an empty collection when no environments exist', function () {
    // Delete all environments
    EnvironmentModel::query()->delete();

    $result = $this->getAllEnvironmentsAction->handle();

    expect($result)->toBeInstanceOf(EnvironmentCollection::class)
        ->and($result)->toBeEmpty();
});
