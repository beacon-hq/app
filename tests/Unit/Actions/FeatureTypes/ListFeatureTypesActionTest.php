<?php

declare(strict_types=1);

use App\Actions\AppContext\SetAppContext;
use App\Actions\FeatureTypes\ListFeatureTypes;
use App\Models\FeatureType;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::beginTransaction();

    [$this->team, , $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

    // Delete any existing feature types for this team
    FeatureType::query()->where('team_id', $this->team->id)->delete();

    // Create feature types in non-alphabetical order
    $this->featureTypeC = FeatureType::factory()->for($this->team)->create([
        'name' => 'C Feature Type',
        'description' => 'Description for C',
        'color' => '#ff0000',
        'icon' => 'Star',
        'is_default' => true,
    ]);

    $this->featureTypeA = FeatureType::factory()->for($this->team)->create([
        'name' => 'A Feature Type',
        'description' => 'Description for A',
        'color' => '#00ff00',
        'icon' => 'Flag',
        'is_default' => false,
    ]);

    $this->featureTypeB = FeatureType::factory()->for($this->team)->create([
        'name' => 'B Feature Type',
        'description' => 'Description for B',
        'color' => '#0000ff',
        'icon' => 'Rocket',
        'is_default' => false,
    ]);

    $this->listFeatureTypesAction = ListFeatureTypes::make();
});

afterEach(function () {
    DB::rollBack();
});

it('returns all feature types sorted by name', function () {
    $result = $this->listFeatureTypesAction->handle();

    expect($result)->toHaveCount(3)
        ->and($result[0]->name)->toBe('A Feature Type')
        ->and($result[1]->name)->toBe('B Feature Type')
        ->and($result[2]->name)->toBe('C Feature Type');
});

it('returns an empty collection when no feature types exist', function () {
    // Delete all feature types
    FeatureType::query()->delete();

    $result = $this->listFeatureTypesAction->handle();

    expect($result)->toHaveCount(0)
        ->and($result)->toBeEmpty();
});

it('includes all feature type properties in the returned collection', function () {
    $result = $this->listFeatureTypesAction->handle();

    // Find the feature type A in the result
    $featureTypeA = $result->first(fn ($item) => $item->name === 'A Feature Type');

    expect($featureTypeA)->not->toBeNull()
        ->and($featureTypeA->id)->toBe($this->featureTypeA->id)
        ->and($featureTypeA->description)->toBe('Description for A')
        ->and($featureTypeA->color)->toBe('#00ff00')
        ->and($featureTypeA->icon)->toBe('Flag')
        ->and($featureTypeA->isDefault)->toBeFalse();

    // Find the default feature type in the result
    $defaultFeatureType = $result->first(fn ($item) => $item->isDefault);

    expect($defaultFeatureType)->not->toBeNull()
        ->and($defaultFeatureType->id)->toBe($this->featureTypeC->id)
        ->and($defaultFeatureType->name)->toBe('C Feature Type')
        ->and($defaultFeatureType->isDefault)->toBeTrue();
});
