<?php

declare(strict_types=1);

use App\Actions\AppContext\SetAppContext;
use App\Actions\FeatureTypes\FindFeatureType;
use App\Actions\FeatureTypes\GetDefaultFeatureType;
use App\Models\FeatureType;
use App\Values\FeatureType as FeatureTypeValue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use function Pest\Laravel\mock;

beforeEach(function () {
    [$this->team, , $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

    // Create a feature type
    $this->featureType = FeatureType::factory()->for($this->team)->create([
        'name' => 'Test Feature Type',
        'description' => 'For testing purposes',
        'color' => '#ff0000',
        'icon' => 'Star',
        'temporary' => false,
        'is_default' => false,
    ]);

    // Mock the GetDefaultFeatureTypeAction
    $this->getDefaultFeatureTypeAction = mock(GetDefaultFeatureType::class);

    // Create the FindFeatureTypeAction with the mocked dependency
    $this->findFeatureTypeAction = new FindFeatureType(
        $this->getDefaultFeatureTypeAction
    );
});

it('can find a feature type by ID', function () {
    $result = $this->findFeatureTypeAction->handle($this->featureType->id);

    expect($result->id)->toBe($this->featureType->id)
        ->and($result->name)->toBe('Test Feature Type')
        ->and($result->description)->toBe('For testing purposes')
        ->and($result->color)->toBe('#ff0000')
        ->and($result->icon)->toBe('Star')
        ->and($result->temporary)->toBeFalse()
        ->and($result->isDefault)->toBeFalse();
});

it('returns the default feature type when no ID is provided', function () {
    // Create a mock default feature type value object
    $defaultFeatureType = FeatureTypeValue::from(
        'default-id',
        'Default Feature Type',
        'Default for testing',
        'Flag',
        '#00ff00',
        false,
        true
    );

    // Set up the mock to return our default feature type
    $this->getDefaultFeatureTypeAction->shouldReceive('handle')
        ->once()
        ->withNoArgs()
        ->andReturn($defaultFeatureType);

    // Call the action with null ID
    $result = $this->findFeatureTypeAction->handle(null);

    // Verify the result is our default feature type
    expect($result)->toBe($defaultFeatureType)
        ->and($result->id)->toBe('default-id')
        ->and($result->name)->toBe('Default Feature Type')
        ->and($result->isDefault)->toBeTrue();
});

it('throws an exception when feature type does not exist', function () {
    $this->findFeatureTypeAction->handle('non-existent-id');
})->throws(ModelNotFoundException::class);
