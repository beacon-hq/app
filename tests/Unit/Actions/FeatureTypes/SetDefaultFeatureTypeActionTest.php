<?php

declare(strict_types=1);

use App\Actions\AppContext\SetAppContext;
use App\Actions\FeatureTypes\SetDefaultFeatureType;
use App\Models\FeatureType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::beginTransaction();

    [$this->team, , $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

    // Create feature types for testing
    $this->defaultFeatureType = FeatureType::factory()->for($this->team)->create([
        'name' => 'Default Feature Type',
        'description' => 'This is initially the default',
        'color' => '#ff0000',
        'icon' => 'Star',
        'is_default' => true,
    ]);

    $this->nonDefaultFeatureType = FeatureType::factory()->for($this->team)->create([
        'name' => 'Non-Default Feature Type',
        'description' => 'This will be set as default',
        'color' => '#00ff00',
        'icon' => 'Flag',
        'is_default' => false,
    ]);

    $this->anotherNonDefaultFeatureType = FeatureType::factory()->for($this->team)->create([
        'name' => 'Another Non-Default Feature Type',
        'description' => 'This will remain non-default',
        'color' => '#0000ff',
        'icon' => 'Rocket',
        'is_default' => false,
    ]);

    $this->setDefaultFeatureTypeAction = SetDefaultFeatureType::make();
});

afterEach(function () {
    DB::rollBack();
});

it('sets a feature type as default', function () {
    $result = $this->setDefaultFeatureTypeAction->handle($this->nonDefaultFeatureType->id);

    // Refresh models from the database
    $this->defaultFeatureType->refresh();
    $this->nonDefaultFeatureType->refresh();
    $this->anotherNonDefaultFeatureType->refresh();

    // Verify the non-default feature type was set as default
    expect($this->nonDefaultFeatureType->is_default)->toBeTrue()
        ->and($result->id)->toBe($this->nonDefaultFeatureType->id)
        ->and($result->isDefault)->toBeTrue();

    // Verify the previously default feature type is no longer default
    expect($this->defaultFeatureType->is_default)->toBeFalse();

    // Verify the other non-default feature type remains non-default
    expect($this->anotherNonDefaultFeatureType->is_default)->toBeFalse();

    // Verify only one feature type is default
    $defaultCount = FeatureType::where('is_default', true)->count();
    expect($defaultCount)->toBe(1);
});

it('maintains default status if already default', function () {
    $result = $this->setDefaultFeatureTypeAction->handle($this->defaultFeatureType->id);

    // Refresh models from the database
    $this->defaultFeatureType->refresh();
    $this->nonDefaultFeatureType->refresh();

    // Verify the default feature type remains default
    expect($this->defaultFeatureType->is_default)->toBeTrue()
        ->and($result->id)->toBe($this->defaultFeatureType->id)
        ->and($result->isDefault)->toBeTrue();

    // Verify the non-default feature type remains non-default
    expect($this->nonDefaultFeatureType->is_default)->toBeFalse();

    // Verify only one feature type is default
    $defaultCount = FeatureType::where('is_default', true)->count();
    expect($defaultCount)->toBe(1);
});

it('throws an exception when feature type does not exist', function () {
    $this->setDefaultFeatureTypeAction->handle('non-existent-id');
})->throws(ModelNotFoundException::class);
