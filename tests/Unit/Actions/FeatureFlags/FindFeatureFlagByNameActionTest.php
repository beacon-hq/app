<?php

declare(strict_types=1);

use App\Actions\FeatureFlags\FindFeatureFlagByName;
use App\Models\Application;
use App\Models\Environment;
use App\Models\FeatureFlag;
use App\Models\FeatureFlagStatus;
use App\Models\FeatureType;
use App\Models\Policy;
use App\Models\Tag;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\Unit\Actions\AppContext\SetAppContext;

beforeEach(function () {
    [$this->team, , $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

    // Clean up any existing feature flags with the test name
    FeatureFlag::query()->where('name', 'unique-test-flag')->delete();

    // Create related models
    $this->featureType = FeatureType::factory()->for($this->team)->create([
        'name' => 'Test Type',
        'description' => 'Type for testing',
        'color' => '#ff0000',
        'icon' => 'Flag',
    ]);

    $this->application = Application::factory()->for($this->team)->create([
        'name' => 'test-app',
        'display_name' => 'Test App',
    ]);

    $this->environment = Environment::factory()->for($this->team)->create([
        'name' => 'test-env',
        'description' => 'Test Environment',
    ]);

    $this->policy = Policy::factory()->for($this->team)->create([
        'name' => 'test-policy',
        'description' => 'Test Policy',
    ]);

    $this->tags = Tag::factory()->for($this->team)->count(2)->create();

    // Create feature flag with a unique name
    $this->featureFlag = FeatureFlag::factory()
        ->for($this->team)
        ->for($this->featureType)
        ->create([
            'name' => 'unique-test-flag',
            'description' => 'Test feature flag with unique name',
        ]);

    // Attach tags
    $this->featureFlag->tags()->attach($this->tags->pluck('id'));

    // Create feature flag status
    $this->featureFlagStatus = FeatureFlagStatus::factory()->create([
        'feature_flag_id' => $this->featureFlag->id,
        'application_id' => $this->application->id,
        'environment_id' => $this->environment->id,
        'status' => true,
    ]);

    $this->featureFlag->statuses()->attach($this->featureFlagStatus);

    $this->findFeatureFlagByNameAction = FindFeatureFlagByName::make();
});

it('can find a feature flag by name with all relations', function () {
    $result = $this->findFeatureFlagByNameAction->handle('unique-test-flag');

    // Test basic properties
    expect($result->id)->toBe($this->featureFlag->id)
        ->and($result->name)->toBe('unique-test-flag')
        ->and($result->description)->toBe('Test feature flag with unique name');

    // Test feature type relation
    expect($result->featureType->id)->toBe($this->featureType->id)
        ->and($result->featureType->name)->toBe('Test Type')
        ->and($result->featureType->color)->toBe('#ff0000')
        ->and($result->featureType->icon)->toBe('Flag');

    // Test tags relation
    expect($result->tags)->toHaveCount(2)
        ->and($result->tags->pluck('id'))->toContain($this->tags->first()->id)
        ->and($result->tags->pluck('id'))->toContain($this->tags->last()->id);

    // Test statuses relation with application and environment
    expect($result->statuses)->toHaveCount(1)
        ->and($result->statuses->first()->application->id)->toBe($this->application->id)
        ->and($result->statuses->first()->environment->id)->toBe($this->environment->id)
        ->and($result->statuses->first()->status)->toBeTrue();
});

it('throws an exception when feature flag with given name does not exist', function () {
    $this->findFeatureFlagByNameAction->handle('non-existent-flag-name');
})->throws(ModelNotFoundException::class);
