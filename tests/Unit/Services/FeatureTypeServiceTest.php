<?php

declare(strict_types=1);

use App\Models\FeatureType;
use App\Models\Team;
use App\Services\FeatureTypeService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

beforeEach(function () {
    $this->team = Team::factory()->create();
    $this->featureTypeService = app(FeatureTypeService::class);
});

it('can set a feature type as default', function () {
    $featureType1 = FeatureType::factory()->for($this->team)->create(['is_default' => true]);
    $featureType2 = FeatureType::factory()->for($this->team)->create(['is_default' => false]);

    $result = $this->featureTypeService->setAsDefault($featureType2->id);

    $featureType1->refresh();
    $featureType2->refresh();

    expect($featureType1->is_default)->toBeFalse()
        ->and($featureType2->is_default)->toBeTrue()
        ->and($result->isDefault)->toBeTrue()
        ->and($result->id)->toBe($featureType2->id);
});

it('ensures only one feature type can be default at a time', function () {
    $featureType1 = FeatureType::factory()->for($this->team)->create(['is_default' => true]);
    $featureType2 = FeatureType::factory()->for($this->team)->create(['is_default' => false]);
    $featureType3 = FeatureType::factory()->for($this->team)->create(['is_default' => false]);

    $this->featureTypeService->setAsDefault($featureType2->id);

    expect(FeatureType::where('is_default', true)->count())->toBe(1)
        ->and($featureType2->fresh()->is_default)->toBeTrue()
        ->and($featureType1->fresh()->is_default)->toBeFalse()
        ->and($featureType3->fresh()->is_default)->toBeFalse();
});

it('can get the default feature type', function () {
    FeatureType::query()->delete();
    $defaultFeatureType = FeatureType::factory()->for($this->team)->create(['is_default' => true]);
    FeatureType::factory()->for($this->team)->create(['is_default' => false]);

    $result = $this->featureTypeService->getDefault();

    expect($result->id)
        ->toBe($defaultFeatureType->id)
        ->and($result->isDefault)
        ->toBeTrue();
});

it('throws exception when no default feature type exists', function () {
    FeatureType::query()->delete();
    FeatureType::factory()->for($this->team)->create(['is_default' => false]);

    $this->featureTypeService->getDefault();
})->throws(ModelNotFoundException::class);

it('can create a feature type with default flag', function () {
    $existingDefault = FeatureType::factory()->for($this->team)->create(['is_default' => true]);

    $featureTypeData = \App\Values\FeatureType::from(
        id: '',
        name: 'New Default Type',
        description: 'Test description',
        icon: 'Rocket',
        color: '#ff0000',
        temporary: false,
        isDefault: true
    );

    $result = $this->featureTypeService->create($featureTypeData);

    $existingDefault->refresh();

    expect($result->isDefault)->toBeTrue()
        ->and($existingDefault->is_default)->toBeFalse()
        ->and(FeatureType::where('is_default', true)->count())->toBe(1);
});

it('can update a feature type to be default', function () {
    $existingDefault = FeatureType::factory()->for($this->team)->create(['is_default' => true]);
    $featureType = FeatureType::factory()->for($this->team)->create(['is_default' => false]);

    $featureTypeData = \App\Values\FeatureType::from(
        id: $featureType->id,
        name: $featureType->name,
        description: $featureType->description,
        icon: $featureType->icon,
        color: $featureType->color,
        temporary: $featureType->temporary,
        isDefault: true
    );

    $result = $this->featureTypeService->update($featureTypeData);

    $existingDefault->refresh();

    expect($result->isDefault)->toBeTrue()
        ->and($existingDefault->is_default)->toBeFalse()
        ->and(FeatureType::where('is_default', true)->count())->toBe(1);
});
