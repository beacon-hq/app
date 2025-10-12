<?php

declare(strict_types=1);

use App\Actions\FeatureTypes\UpdateFeatureType;
use App\Models\FeatureType;
use App\Values\FeatureType as FeatureTypeValue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Tests\Unit\Actions\AppContext\SetAppContext;

beforeEach(function () {
    DB::beginTransaction();

    [$this->team, , $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

    // Create a feature type to update
    $this->featureType = FeatureType::factory()->for($this->team)->create([
        'name' => 'Original Feature Type',
        'description' => 'Original description',
        'color' => '#ff0000',
        'icon' => 'Star',
        'temporary' => false,
        'is_default' => false,
    ]);

    $this->updateFeatureTypeAction = UpdateFeatureType::make();
});

afterEach(function () {
    DB::rollBack();
});

it('can update a feature type', function () {
    $featureType = FeatureTypeValue::from(
        id: $this->featureType->id,
        name: 'Updated Feature Type',
        description: 'Updated description',
        icon: 'Rocket',
        color: '#00ff00',
        temporary: true,
        isDefault: false
    );

    $result = $this->updateFeatureTypeAction->handle($featureType);

    // Refresh the model from the database
    $this->featureType->refresh();

    // Verify the feature type was updated in the database
    expect($this->featureType->name)->toBe('Updated Feature Type')
        ->and($this->featureType->description)->toBe('Updated description')
        ->and($this->featureType->is_default)->toBeFalse()
        ->and($result->name)->toBe('Updated Feature Type')
        ->and($result->description)->toBe('Updated description')
        ->and($result->isDefault)->toBeFalse()
        ->and($result->id)->toBe($this->featureType->id);
});

it('can update a feature type to be default', function () {
    $featureType = FeatureTypeValue::from(
        id: $this->featureType->id,
        name: 'Default Feature Type',
        description: 'Default feature type description',
        icon: 'Flag',
        color: '#0000ff',
        temporary: false,
        isDefault: true
    );

    $result = $this->updateFeatureTypeAction->handle($featureType);

    // Refresh the model from the database
    $this->featureType->refresh();

    // Verify the feature type was updated to be default
    expect($this->featureType->name)->toBe('Default Feature Type')
        ->and($this->featureType->description)->toBe('Default feature type description')
        ->and($this->featureType->is_default)->toBeTrue()
        ->and($result->name)->toBe('Default Feature Type')
        ->and($result->description)->toBe('Default feature type description')
        ->and($result->isDefault)->toBeTrue()
        ->and($result->id)->toBe($this->featureType->id);
});

it('throws an exception when feature type does not exist', function () {
    $featureType = FeatureTypeValue::from(
        id: 'non-existent-id',
        name: 'Non-existent Feature Type',
        description: 'This feature type does not exist',
        icon: 'Question',
        color: '#ff00ff',
        temporary: false,
        isDefault: false
    );

    $this->updateFeatureTypeAction->handle($featureType);
})->throws(ModelNotFoundException::class);
