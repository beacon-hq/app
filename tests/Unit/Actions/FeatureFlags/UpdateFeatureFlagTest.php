<?php

declare(strict_types=1);

use App\Actions\FeatureFlags\UpdateFeatureFlag;
use App\Enums\PolicyDefinitionMatchOperator;
use App\Enums\PolicyDefinitionType;
use App\Enums\RolloutStrategy;
use App\Models\Application;
use App\Models\Environment;
use App\Models\FeatureFlag;
use App\Models\FeatureFlagStatus;
use App\Models\FeatureType;
use App\Models\Tag;
use App\Values\Application as ApplicationValue;
use App\Values\Collections\PolicyDefinitionCollection;
use App\Values\Environment as EnvironmentValue;
use App\Values\FeatureFlag as FeatureFlagValue;
use App\Values\FeatureFlagStatus as FeatureFlagStatusValue;
use App\Values\FeatureType as FeatureTypeValue;
use App\Values\PolicyDefinition;
use App\Values\Tag as TagValue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\Unit\Actions\AppContext\SetAppContext;

beforeEach(function () {
    [$this->team, , $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

    // Create feature types
    $this->featureTypeA = FeatureType::factory()->for($this->team)->create([
        'name' => 'Type A',
    ]);

    $this->featureTypeB = FeatureType::factory()->for($this->team)->create([
        'name' => 'Type B',
    ]);

    // Create applications
    $this->applicationA = Application::factory()->for($this->team)->create([
        'name' => 'app-a',
        'display_name' => 'App A',
    ]);

    $this->applicationB = Application::factory()->for($this->team)->create([
        'name' => 'app-b',
        'display_name' => 'App B',
    ]);

    // Create environments
    $this->environmentA = Environment::factory()->for($this->team)->create([
        'name' => 'env-a',
        'description' => 'Environment A',
    ]);

    $this->environmentB = Environment::factory()->for($this->team)->create([
        'name' => 'env-b',
        'description' => 'Environment B',
    ]);

    // Create tags
    $this->tagA = Tag::factory()->for($this->team)->create([
        'name' => 'Tag A',
        'color' => '#ff0000',
    ]);

    $this->tagB = Tag::factory()->for($this->team)->create([
        'name' => 'Tag B',
        'color' => '#00ff00',
    ]);

    $this->tagC = Tag::factory()->for($this->team)->create([
        'name' => 'Tag C',
        'color' => '#0000ff',
    ]);

    // Create a feature flag for testing
    $this->featureFlag = FeatureFlag::factory()
        ->for($this->team)
        ->for($this->featureTypeA, 'featureType')
        ->create([
            'name' => 'test-flag',
            'description' => 'Test Feature Flag',
            'status' => true,
        ]);

    // Attach a tag to the feature flag
    $this->featureFlag->tags()->attach($this->tagA->id);

    // Create a feature flag status
    $this->featureFlagStatus = FeatureFlagStatus::factory()->create([
        'feature_flag_id' => $this->featureFlag->id,
        'application_id' => $this->applicationA->id,
        'environment_id' => $this->environmentA->id,
        'status' => true,
        'definition' => PolicyDefinitionCollection::make([
            PolicyDefinition::from(
                type: PolicyDefinitionType::EXPRESSION,
                subject: 'role',
                operator: PolicyDefinitionMatchOperator::EQUAL,
                values: ['admin']
            ),
        ]),
    ]);

    $this->updateFeatureFlagAction = UpdateFeatureFlag::make();
});

it('throws an exception when feature flag does not exist', function () {
    $featureFlagValue = FeatureFlagValue::withoutValidation([
        'id' => 'non-existent-id',
    ]);

    $this->updateFeatureFlagAction->handle($featureFlagValue);
})->throws(ModelNotFoundException::class);

it('updates basic feature flag properties', function () {
    // Create a feature flag value with updated properties
    $featureFlagValue = FeatureFlagValue::from($this->featureFlag)->with(description: 'Updated description', status: false);

    $updatedFlag = $this->updateFeatureFlagAction->handle($featureFlagValue);

    expect($updatedFlag)->toBeInstanceOf(FeatureFlagValue::class)
        ->and($updatedFlag->id)->toBe($this->featureFlag->id)
        ->and($updatedFlag->name)->toBe('test-flag') // Name should not be changed
        ->and($updatedFlag->description)->toBe('Updated description')
        ->and($updatedFlag->status)->toBeFalse();

    // Verify in database
    $this->featureFlag->refresh();
    expect($this->featureFlag->description)->toBe('Updated description')
        ->and($this->featureFlag->status)->toBeFalse();
});

it('updates feature type', function () {
    // Create a feature flag value with updated feature type
    $featureFlagValue = FeatureFlagValue::from([
        'id' => $this->featureFlag->id,
        'featureType' => FeatureTypeValue::withoutValidation([
            'id' => $this->featureTypeB->id,
            'name' => $this->featureTypeB->name,
        ]),
    ]);

    $updatedFlag = $this->updateFeatureFlagAction->handle($featureFlagValue);

    expect($updatedFlag)->toBeInstanceOf(FeatureFlagValue::class)
        ->and($updatedFlag->featureType->id)->toBe($this->featureTypeB->id);

    // Verify in database
    $this->featureFlag->refresh();
    expect($this->featureFlag->feature_type_id)->toBe($this->featureTypeB->id);
});

it('updates tags', function () {
    // Create a feature flag value with updated tags
    $featureFlagValue = FeatureFlagValue::withoutValidation([
        'id' => $this->featureFlag->id,
        'tags' => collect([
            TagValue::withoutValidation(['id' => $this->tagB->id, 'name' => $this->tagB->name]),
            TagValue::withoutValidation(['id' => $this->tagC->id, 'name' => $this->tagC->name]),
        ]),
        'featureType' => $this->featureFlag->featureType
    ]);

    $updatedFlag = $this->updateFeatureFlagAction->handle($featureFlagValue);

    expect($updatedFlag)->toBeInstanceOf(FeatureFlagValue::class)
        ->and($updatedFlag->tags)->toHaveCount(2)
        ->and($updatedFlag->tags->pluck('id'))->toContain($this->tagB->id)
        ->and($updatedFlag->tags->pluck('id'))->toContain($this->tagC->id)
        ->and($updatedFlag->tags->pluck('id'))->not->toContain($this->tagA->id);

    // Verify in database
    $this->featureFlag->refresh();
    $tagIds = $this->featureFlag->tags->pluck('id')->toArray();
    expect($tagIds)->toContain($this->tagB->id)
        ->and($tagIds)->toContain($this->tagC->id)
        ->and($tagIds)->not->toContain($this->tagA->id);
});

it('updates feature flag statuses', function () {
    // Create a new feature flag status value
    $newStatus = FeatureFlagStatusValue::from([
        'application' => ApplicationValue::withoutValidation([
            'id' => $this->applicationB->id,
            'name' => $this->applicationB->name,
        ]),
        'environment' => EnvironmentValue::withoutValidation([
            'id' => $this->environmentB->id,
            'name' => $this->environmentB->name,
        ]),
        'status' => true,
        'definition' => PolicyDefinitionCollection::make([
            PolicyDefinition::from(
                type: PolicyDefinitionType::EXPRESSION,
                subject: 'role',
                operator: PolicyDefinitionMatchOperator::EQUAL,
                values: ['user']
            ),
        ]),
        'rolloutPercentage' => 50,
        'rolloutStrategy' => 'random',
    ]);

    // Create a feature flag value with updated statuses
    $featureFlagValue = FeatureFlagValue::from([
        'id' => $this->featureFlag->id,
        'statuses' => collect([$newStatus]),
        'featureType' => $this->featureFlag->featureType
    ]);

    $updatedFlag = $this->updateFeatureFlagAction->handle($featureFlagValue);

    expect($updatedFlag)->toBeInstanceOf(FeatureFlagValue::class)
        ->and($updatedFlag->statuses)->toHaveCount(1)
        ->and($updatedFlag->statuses[0]->application->id)->toBe($this->applicationB->id)
        ->and($updatedFlag->statuses[0]->environment->id)->toBe($this->environmentB->id)
        ->and($updatedFlag->statuses[0]->status)->toBeTrue()
        ->and($updatedFlag->statuses[0]->rolloutPercentage)->toBe(50)
        ->and($updatedFlag->statuses[0]->rolloutStrategy)->toBe(RolloutStrategy::RANDOM);

    // Verify in database
    $this->featureFlag->refresh();
    $this->featureFlag->load('statuses');
    expect($this->featureFlag->statuses)->toHaveCount(1);

    $status = $this->featureFlag->statuses->first();
    expect($status->application_id)->toBe($this->applicationB->id)
        ->and($status->environment_id)->toBe($this->environmentB->id)
        ->and($status->status)->toBeTrue()
        ->and($status->rollout_percentage)->toBe(50)
        ->and($status->rollout_strategy)->toBe(RolloutStrategy::RANDOM);

    // The old status should be deleted
    $oldStatus = FeatureFlagStatus::find($this->featureFlagStatus->id);
    expect($oldStatus)->toBeNull();
});

it('updates an existing feature flag status', function () {
    // Create a feature flag status value that updates the existing status
    $updatedStatus = FeatureFlagStatusValue::from([
        'id' => $this->featureFlagStatus->id,
        'application' => ApplicationValue::withoutValidation([
            'id' => $this->applicationA->id,
            'name' => $this->applicationA->name,
        ]),
        'environment' => EnvironmentValue::withoutValidation([
            'id' => $this->environmentA->id,
            'name' => $this->environmentA->name,
        ]),
        'status' => false,
        'definition' => PolicyDefinitionCollection::make([
            PolicyDefinition::from(
                type: PolicyDefinitionType::EXPRESSION,
                subject: 'email',
                operator: PolicyDefinitionMatchOperator::CONTAINS_ANY,
                values: ['example.com']
            ),
        ]),
        'rolloutPercentage' => 75,
        'rolloutStrategy' => 'context',
        'rolloutContext' => ['user_id'],
    ]);

    // Create a feature flag value with updated statuses
    $featureFlagValue = FeatureFlagValue::from([
        'id' => $this->featureFlag->id,
        'statuses' => collect([$updatedStatus]),
        'featureType' => $this->featureFlag->featureType
    ]);

    $updatedFlag = $this->updateFeatureFlagAction->handle($featureFlagValue);

    expect($updatedFlag)->toBeInstanceOf(FeatureFlagValue::class)
        ->and($updatedFlag->statuses)->toHaveCount(1)
        ->and($updatedFlag->statuses[0]->id)->toBe($this->featureFlagStatus->id)
        ->and($updatedFlag->statuses[0]->application->id)->toBe($this->applicationA->id)
        ->and($updatedFlag->statuses[0]->environment->id)->toBe($this->environmentA->id)
        ->and($updatedFlag->statuses[0]->status)->toBeFalse()
        ->and($updatedFlag->statuses[0]->rolloutPercentage)->toBe(75)
        ->and($updatedFlag->statuses[0]->rolloutStrategy)->toBe(RolloutStrategy::CONTEXT)
        ->and($updatedFlag->statuses[0]->rolloutContext->toArray())->toBe(['user_id']);

    // Verify in database
    $this->featureFlagStatus->refresh();
    expect($this->featureFlagStatus->status)->toBeFalse()
        ->and($this->featureFlagStatus->rollout_percentage)->toBe(75)
        ->and($this->featureFlagStatus->rollout_strategy)->toBe(RolloutStrategy::CONTEXT)
        ->and($this->featureFlagStatus->rollout_context)->toBe(['user_id']);
});

it('removes all statuses when statuses is empty', function () {
    // Create a feature flag value with empty statuses
    $featureFlagValue = FeatureFlagValue::from([
        'id' => $this->featureFlag->id,
        'statuses' => collect(),
        'featureType' => $this->featureFlag->featureType
    ]);

    $updatedFlag = $this->updateFeatureFlagAction->handle($featureFlagValue);

    expect($updatedFlag)->toBeInstanceOf(FeatureFlagValue::class)
        ->and($updatedFlag->statuses)->toHaveCount(0);

    // Verify in database
    $this->featureFlag->refresh();
    $this->featureFlag->load('statuses');
    expect($this->featureFlag->statuses)->toHaveCount(0);

    // The old status should be deleted
    $oldStatus = FeatureFlagStatus::find($this->featureFlagStatus->id);
    expect($oldStatus)->toBeNull();
});
