<?php

declare(strict_types=1);

use App\Actions\FeatureTypes\GetDefaultFeatureType;
use App\Models\FeatureType;
use Illuminate\Support\Facades\DB;
use Tests\Unit\Actions\AppContext\SetAppContext;

beforeEach(function () {
    DB::beginTransaction();

    [$this->team, , $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

    $this->getDefaultFeatureTypeAction = GetDefaultFeatureType::make();
});

afterEach(function () {
    DB::rollBack();
});

it('returns the default feature type when one exists', function () {
    // Delete any existing feature types for this team
    FeatureType::query()->where('team_id', $this->team->id)->delete();

    // Create a default feature type
    $defaultFeatureType = FeatureType::factory()->for($this->team)->create([
        'name' => 'Default Feature Type',
        'description' => 'Default feature type for testing',
        'color' => '#ff0000',
        'icon' => 'Star',
        'temporary' => false,
        'is_default' => true,
    ]);

    // Create a non-default feature type
    FeatureType::factory()->for($this->team)->create([
        'name' => 'Non-Default Feature Type',
        'is_default' => false,
    ]);

    // Call the action
    $result = $this->getDefaultFeatureTypeAction->handle();

    // Verify the result is the default feature type
    expect($result->id)->toBe($defaultFeatureType->id)
        ->and($result->name)->toBe('Default Feature Type')
        ->and($result->description)->toBe('Default feature type for testing')
        ->and($result->isDefault)->toBeTrue();
});

it('returns the first feature type when no default exists', function () {
    // Create feature types with no default
    FeatureType::query()->delete(); // Ensure no leftover feature types

    $firstFeatureType = FeatureType::factory()->for($this->team)->create([
        'name' => 'First Feature Type',
        'description' => 'First feature type with no default',
        'is_default' => false,
    ]);

    FeatureType::factory()->for($this->team)->create([
        'name' => 'Second Feature Type',
        'is_default' => false,
    ]);

    // Call the action
    $result = $this->getDefaultFeatureTypeAction->handle();

    // Verify the result is the first feature type
    expect($result->id)->toBe($firstFeatureType->id)
        ->and($result->name)->toBe('First Feature Type')
        ->and($result->isDefault)->toBeFalse();
});

it('throws an exception when no feature types exist', function () {
    // Delete all feature types
    FeatureType::query()->delete();

    // Call the action - should throw an exception
    $this->getDefaultFeatureTypeAction->handle();
})->throws(\RuntimeException::class, 'No feature types found in the system.');
