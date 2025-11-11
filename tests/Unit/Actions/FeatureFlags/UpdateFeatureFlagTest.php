<?php

declare(strict_types=1);

use App\Actions\AppContext\SetAppContext;
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
use App\Values\Collections\FeatureFlagStatusCollection;
use App\Values\Collections\PolicyDefinitionCollection;
use App\Values\Collections\TagCollection;
use App\Values\Environment as EnvironmentValue;
use App\Values\FeatureFlag as FeatureFlagValue;
use App\Values\FeatureFlagStatus as FeatureFlagStatusValue;
use App\Values\FeatureType as FeatureTypeValue;
use App\Values\PolicyDefinition;
use App\Values\Tag as TagValue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    [$this->team, , $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

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
    $this->initialFeatureType = FeatureType::factory()->for($this->team)->create([
        'name' => 'Initial Type',
        'description' => 'Initial feature type',
        'color' => '#ff0000',
        'icon' => 'Flag',
    ]);

    $this->newFeatureType = FeatureType::factory()->for($this->team)->create([
        'name' => 'Updated Type',
        'description' => 'Updated feature type',
        'color' => '#00ff00',
        'icon' => 'Rocket',
    ]);

    $this->initialApplication = Application::factory()->for($this->team)->create([
        'name' => 'initial-app',
        'display_name' => 'Initial App',
    ]);

    $this->initialEnvironment = Environment::factory()->for($this->team)->create([
        'name' => 'initial-env',
        'description' => 'Initial Environment',
    ]);

    $this->newApplication = Application::factory()->for($this->team)->create([
        'name' => 'new-app',
        'display_name' => 'New App',
    ]);

    $this->newEnvironment = Environment::factory()->for($this->team)->create([
        'name' => 'new-env',
        'description' => 'New Environment',
    ]);

    $this->initialTags = collect();
    for ($i = 0; $i < 2; $i++) {
        $this->initialTags->push(Tag::factory()->for($this->team)->create([
            'name' => "initial-tag-{$i}",
        ]));
    }

    $this->newTags = collect();
    for ($i = 0; $i < 3; $i++) {
        $this->newTags->push(Tag::factory()->for($this->team)->create([
            'name' => "new-tag-{$i}",
        ]));
    }

    $this->initialFeatureFlag = FeatureFlag::factory()
        ->for($this->team)
        ->for($this->initialFeatureType)
        ->create([
            'name' => 'initial-flag',
            'description' => 'Initial feature flag description',
        ]);

    $this->initialFeatureFlag->tags()->attach($this->initialTags->pluck('id'));

    $this->initialStatus = FeatureFlagStatus::factory()->create([
        'feature_flag_id' => $this->initialFeatureFlag->id,
        'application_id' => $this->initialApplication->id,
        'environment_id' => $this->initialEnvironment->id,
        'status' => false,
    ]);

    $this->updateFeatureFlagAction = UpdateFeatureFlag::make();
});

it('can update a feature flag with all relationships', function () {
    DB::beginTransaction();

    try {
        $newTags = TagCollection::make($this->newTags->map(fn ($tag) => TagValue::from(
            id: $tag->id,
            name: $tag->name,
            color: $tag->color
        )));

        $newApplication = ApplicationValue::from(
            id: $this->newApplication->id,
            name: $this->newApplication->name,
            displayName: $this->newApplication->display_name,
            description: $this->newApplication->description,
            environments: null,
            color: $this->newApplication->color
        );

        $newEnvironment = EnvironmentValue::from(
            id: $this->newEnvironment->id,
            name: $this->newEnvironment->name,
            description: $this->newEnvironment->description,
            color: $this->newEnvironment->color
        );

        $newFeatureType = FeatureTypeValue::from(
            id: $this->newFeatureType->id,
            name: $this->newFeatureType->name,
            description: $this->newFeatureType->description,
            icon: $this->newFeatureType->icon,
            color: $this->newFeatureType->color,
            temporary: $this->newFeatureType->temporary,
            isDefault: false
        );

        $newFeatureFlagStatus = FeatureFlagStatusValue::from(
            id: '',
            application: $newApplication,
            environment: $newEnvironment,
            featureFlag: null,
            status: true,
            definition: null
        );

        $updatedFeatureFlag = FeatureFlagValue::from(
            id: $this->initialFeatureFlag->id,
            name: 'updated-flag',
            description: 'Updated feature flag description',
            lastSeenAt: null,
            featureType: $newFeatureType,
            tags: $newTags,
            applications: null,
            statuses: FeatureFlagStatusCollection::make([$newFeatureFlagStatus]),
        );

        $result = $this->updateFeatureFlagAction->handle($updatedFeatureFlag);

        $this->initialFeatureFlag->refresh();

        expect($this->initialFeatureFlag->name)
            ->toBe('initial-flag')
            ->and($this->initialFeatureFlag->description)
            ->toBe('Updated feature flag description')
            ->and($this->initialFeatureFlag->feature_type_id)
            ->toBe($this->newFeatureType->id)
            ->and($result->name)
            ->toBe('initial-flag')
            ->and($result->description)
            ->toBe('Updated feature flag description')
            ->and($result->featureType->id)
            ->toBe($this->newFeatureType->id)
            ->and($this->initialFeatureFlag->tags)
            ->toHaveCount(3)
            ->and($this->initialFeatureFlag->tags->pluck('id'))
            ->not
            ->toContain($this->initialTags->first()->id)
            ->and($this->initialFeatureFlag->tags->pluck('id'))
            ->toContain($this->newTags->first()->id)
            ->and($result->tags)
            ->toHaveCount(3)
            ->and($this->initialFeatureFlag->statuses)
            ->toHaveCount(1)
            ->and($this->initialFeatureFlag->statuses->first()->application_id)
            ->toBe($this->newApplication->id)
            ->and($this->initialFeatureFlag->statuses->first()->environment_id)
            ->toBe($this->newEnvironment->id)
            ->and($this->initialFeatureFlag->statuses->first()->status)
            ->toBeTrue()
            ->and($result->statuses)
            ->toHaveCount(1)
            ->and($result->statuses->first()->application->id)
            ->toBe($this->newApplication->id)
            ->and($result->statuses->first()->environment->id)
            ->toBe($this->newEnvironment->id)
            ->and($result->statuses->first()->status)
            ->toBeTrue();

        $oldStatus = FeatureFlagStatus::where('application_id', $this->initialApplication->id)
            ->where('environment_id', $this->initialEnvironment->id)
            ->where('feature_flag_id', $this->initialFeatureFlag->id)
            ->first();

        expect($oldStatus)->toBeNull();
    } finally {
        DB::rollBack();
    }
});

it('throws an exception when feature flag does not exist', function () {
    $featureFlagValue = FeatureFlagValue::withoutValidation([
        'id' => 'non-existent-id',
    ]);

    $this->updateFeatureFlagAction->handle($featureFlagValue);
})->throws(ModelNotFoundException::class);

it('updates basic feature flag properties', function () {
    $featureFlagValue = FeatureFlagValue::from($this->featureFlag)->with(description: 'Updated description', status: false);

    $updatedFlag = $this->updateFeatureFlagAction->handle($featureFlagValue);

    expect($updatedFlag)->toBeInstanceOf(FeatureFlagValue::class)
        ->and($updatedFlag->id)->toBe($this->featureFlag->id)
        ->and($updatedFlag->name)->toBe('test-flag') // Name should not be changed
        ->and($updatedFlag->description)->toBe('Updated description')
        ->and($updatedFlag->status)->toBeFalse();

    $this->featureFlag->refresh();
    expect($this->featureFlag->description)->toBe('Updated description')
        ->and($this->featureFlag->status)->toBeFalse();
});

it('updates feature type', function () {
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

    $this->featureFlag->refresh();
    expect($this->featureFlag->feature_type_id)->toBe($this->featureTypeB->id);
});

it('updates tags', function () {
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

    $this->featureFlag->refresh();
    $tagIds = $this->featureFlag->tags->pluck('id')->toArray();
    expect($tagIds)->toContain($this->tagB->id)
        ->and($tagIds)->toContain($this->tagC->id)
        ->and($tagIds)->not->toContain($this->tagA->id);
});

it('updates feature flag statuses', function () {
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

    $this->featureFlag->refresh();
    $this->featureFlag->load('statuses');
    expect($this->featureFlag->statuses)->toHaveCount(1);

    $status = $this->featureFlag->statuses->first();
    expect($status->application_id)->toBe($this->applicationB->id)
        ->and($status->environment_id)->toBe($this->environmentB->id)
        ->and($status->status)->toBeTrue()
        ->and($status->rollout_percentage)->toBe(50)
        ->and($status->rollout_strategy)->toBe(RolloutStrategy::RANDOM);

    $oldStatus = FeatureFlagStatus::find($this->featureFlagStatus->id);
    expect($oldStatus)->toBeNull();
});

it('updates an existing feature flag status', function () {
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

    $this->featureFlagStatus->refresh();
    expect($this->featureFlagStatus->status)->toBeFalse()
        ->and($this->featureFlagStatus->rollout_percentage)->toBe(75)
        ->and($this->featureFlagStatus->rollout_strategy)->toBe(RolloutStrategy::CONTEXT)
        ->and($this->featureFlagStatus->rollout_context)->toBe(['user_id']);
});

it('removes all statuses when statuses is empty', function () {
    $featureFlagValue = FeatureFlagValue::from([
        'id' => $this->featureFlag->id,
        'statuses' => collect(),
        'featureType' => $this->featureFlag->featureType
    ]);

    $updatedFlag = $this->updateFeatureFlagAction->handle($featureFlagValue);

    expect($updatedFlag)->toBeInstanceOf(FeatureFlagValue::class)
        ->and($updatedFlag->statuses)->toHaveCount(0);

    $this->featureFlag->refresh();
    $this->featureFlag->load('statuses');
    expect($this->featureFlag->statuses)->toHaveCount(0);

    $oldStatus = FeatureFlagStatus::find($this->featureFlagStatus->id);
    expect($oldStatus)->toBeNull();
});
