<?php

declare(strict_types=1);

use App\Actions\FeatureFlags\TouchFeatureFlag;
use App\Models\FeatureFlag;
use App\Models\FeatureType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\Unit\Actions\AppContext\SetAppContext;

beforeEach(function () {
    [$this->team, , $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

    // Create feature type
    $this->featureType = FeatureType::factory()->for($this->team)->create([
        'name' => 'Test Type',
    ]);

    // Create feature flag
    $this->featureFlag = FeatureFlag::factory()
        ->for($this->team)
        ->for($this->featureType, 'featureType')
        ->create([
            'name' => 'test-flag',
            'description' => 'Test feature flag',
        ]);

    $this->touchFeatureFlagAction = TouchFeatureFlag::make();
});

it('updates the timestamps of a feature flag', function () {
    // Store the original updated_at timestamp
    $originalUpdatedAt = $this->featureFlag->updated_at;

    // Wait a moment to ensure timestamp will be different
    sleep(1);

    // Create value object for the feature flag
    $featureFlagValue = \App\Values\FeatureFlag::from(
        id: $this->featureFlag->id,
        name: $this->featureFlag->name,
        description: $this->featureFlag->description,
        lastSeenAt: null,
        featureType: \App\Values\FeatureType::from(
            id: $this->featureType->id,
            name: $this->featureType->name,
            description: $this->featureType->description,
            icon: $this->featureType->icon,
            color: $this->featureType->color,
            temporary: $this->featureType->temporary,
            isDefault: false
        ),
        tags: null,
        applications: null,
        statuses: null
    );

    // Call the action
    $result = $this->touchFeatureFlagAction->handle($featureFlagValue);

    // Refresh the model from the database
    $this->featureFlag->refresh();

    // Verify the updated_at timestamp has changed
    expect($this->featureFlag->updated_at)->not->toBe($originalUpdatedAt)
        ->and($this->featureFlag->updated_at->timestamp)->toBeGreaterThan($originalUpdatedAt->timestamp)
        ->and($result->id)->toBe($this->featureFlag->id);
});

it('throws an exception when feature flag does not exist', function () {
    // Create a value object with non-existent ID
    $nonExistentFeatureFlag = \App\Values\FeatureFlag::from(
        id: 'non-existent-id',
        name: 'non-existent-flag',
        description: 'Non-existent feature flag',
        lastSeenAt: null,
        featureType: \App\Values\FeatureType::from(
            id: $this->featureType->id,
            name: $this->featureType->name,
            description: $this->featureType->description,
            icon: $this->featureType->icon,
            color: $this->featureType->color,
            temporary: $this->featureType->temporary,
            isDefault: false
        ),
        tags: null,
        applications: null,
        statuses: null
    );

    // Call the action
    $this->touchFeatureFlagAction->handle($nonExistentFeatureFlag);
})->throws(ModelNotFoundException::class);
