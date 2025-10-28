<?php

declare(strict_types=1);

use App\Actions\AppContext\SetAppContext;
use App\Actions\FeatureTypes\CreateFeatureType;
use App\Models\FeatureType;
use App\Values\FeatureType as FeatureTypeValue;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::beginTransaction();

    [$this->team, , $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

    $this->createFeatureTypeAction = CreateFeatureType::make();
});

afterEach(function () {
    DB::rollBack();
});

it('can create a feature type', function () {
    $featureType = FeatureTypeValue::from(
        id: '',
        name: 'Test Feature Type',
        description: 'Feature type for testing',
        icon: 'Star',
        color: '#ff0000',
        temporary: false,
        isDefault: false
    );

    $result = $this->createFeatureTypeAction->handle($featureType);

    // Verify the feature type was created in the database
    $model = FeatureType::where('name', 'Test Feature Type')->first();

    expect($model)->not->toBeNull()
        ->and($model->name)->toBe('Test Feature Type')
        ->and($model->description)->toBe('Feature type for testing')
        ->and($model->is_default)->toBeFalse()
        ->and($model->team_id)->toBe($this->team->id)
        ->and($result->name)->toBe('Test Feature Type')
        ->and($result->description)->toBe('Feature type for testing')
        ->and($result->isDefault)->toBeFalse()
        ->and($result->id)->toBe($model->id);
});

it('can create a default feature type', function () {
    $featureType = FeatureTypeValue::from(
        id: '',
        name: 'Default Feature Type',
        description: 'Default feature type for testing',
        icon: 'Flag',
        color: '#00ff00',
        temporary: false,
        isDefault: true
    );

    $result = $this->createFeatureTypeAction->handle($featureType);

    // Verify the feature type was created in the database
    $model = FeatureType::where('name', 'Default Feature Type')->first();

    expect($model)->not->toBeNull()
        ->and($model->name)->toBe('Default Feature Type')
        ->and($model->description)->toBe('Default feature type for testing')
        ->and($model->is_default)->toBeTrue()
        ->and($result->name)->toBe('Default Feature Type')
        ->and($result->description)->toBe('Default feature type for testing')
        ->and($result->isDefault)->toBeTrue()
        ->and($result->id)->toBe($model->id);
});
