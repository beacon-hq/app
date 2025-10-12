<?php

declare(strict_types=1);

use App\Actions\FeatureTypes\FindFeatureTypeByName;
use App\Models\FeatureType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\Unit\Actions\AppContext\SetAppContext;

beforeEach(function () {
    [$this->team, , $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

    // Create a feature type with a unique name
    $this->featureType = FeatureType::factory()->for($this->team)->create([
        'name' => 'Unique Feature Type Name',
        'description' => 'For testing purposes',
        'color' => '#ff0000',
        'icon' => 'Star',
        'temporary' => false,
        'is_default' => false,
    ]);

    $this->findFeatureTypeByNameAction = FindFeatureTypeByName::make();
});

it('can find a feature type by name', function () {
    $result = $this->findFeatureTypeByNameAction->handle('Unique Feature Type Name');

    expect($result->id)->toBe($this->featureType->id)
        ->and($result->name)->toBe('Unique Feature Type Name')
        ->and($result->description)->toBe('For testing purposes')
        ->and($result->color)->toBe('#ff0000')
        ->and($result->icon)->toBe('Star')
        ->and($result->temporary)->toBeFalse()
        ->and($result->isDefault)->toBeFalse();
});

it('throws an exception when feature type with given name does not exist', function () {
    $this->findFeatureTypeByNameAction->handle('Non-Existent Feature Type');
})->throws(ModelNotFoundException::class);
