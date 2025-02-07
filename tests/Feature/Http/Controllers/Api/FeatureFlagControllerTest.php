<?php

declare(strict_types=1);

use App\Enums\Boolean;
use App\Enums\PolicyDefinitionMatchOperator;
use App\Enums\PolicyDefinitionType;
use App\Models\Application;
use App\Models\Environment;
use App\Models\FeatureFlag;
use App\Models\FeatureFlagStatus;
use App\Models\FeatureType;
use App\Models\Policy;
use App\Models\Tag;
use App\Models\Team;
use App\Models\User;
use App\Values\PolicyDefinition;
use App\Values\PolicyValue;
use Bag\Collection;
use function Pest\Laravel\actingAs;

it('fetches a missing feature flag as inactive', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();

    actingAs($user)
        ->post(route('api.feature-flags.show', ['slug' => 'testing']), [
            'scopeType' => 'testing',
            'scope' => ['testing' => 'test'],
            'appName' => 'testing',
            'environment' => 'testing'
        ])
        ->assertJsonFragment([
            'active' => false
        ])
        ->assertJsonStructure([
            'active',
            'feature_flag',
            'value'
        ]);
});

it('fetches a feature flag without matching app/env as inactive', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    $featureType = FeatureType::factory()->for($team)->create();
    $tags = Tag::factory(3)->for($team)->create();
    $featureFlag = FeatureFlag::factory()->for($team)->for($featureType)->hasAttached($tags)->create();

    actingAs($user)
        ->post(route('api.feature-flags.show', ['slug' => $featureFlag->slug]), [
            'scopeType' => 'testing',
            'scope' => ['testing' => 'test'],
            'appName' => 'testing',
            'environment' => 'testing'
        ])
        ->assertExactJson([
            'feature_flag' => $featureFlag->slug,
            'value' => null,
            'active' => false
        ]);
});

it('fetches a feature flag without matching application as inactive', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    $featureType = FeatureType::factory()->for($team)->create();
    $tags = Tag::factory(3)->for($team)->create();
    $application = Application::factory()->for($team)->create();
    $environment = Environment::factory()->for($team)->create();
    $featureFlag = FeatureFlag::factory()->for($team)->for($featureType)->hasAttached($tags)->create();
    $featureFlagStatus = FeatureFlagStatus::factory()->for($featureFlag)->for($application)->for($environment)->create([
        'status' => true
    ]);
    $featureFlag->statuses()->sync([$featureFlagStatus]);

    actingAs($user)
        ->post(route('api.feature-flags.show', ['slug' => $featureFlag->slug]), [
            'scopeType' => 'testing',
            'scope' => ['testing' => 'test'],
            'appName' => 'testing',
            'environment' => $environment->name,
        ])
        ->assertExactJson([
            'feature_flag' => $featureFlag->slug,
            'value' => null,
            'active' => false
        ]);
});

it('fetches a feature flag without matching environemnt as inactive', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    $featureType = FeatureType::factory()->for($team)->create();
    $tags = Tag::factory(3)->for($team)->create();
    $application = Application::factory()->for($team)->create();
    $environment = Environment::factory()->for($team)->create();
    $featureFlag = FeatureFlag::factory()->for($team)->for($featureType)->hasAttached($tags)->create();
    $featureFlagStatus = FeatureFlagStatus::factory()->for($featureFlag)->for($application)->for($environment)->create([
        'status' => true
    ]);
    $featureFlag->statuses()->sync([$featureFlagStatus]);

    actingAs($user)
        ->post(route('api.feature-flags.show', ['slug' => $featureFlag->slug]), [
            'scopeType' => 'testing',
            'scope' => ['testing' => 'test'],
            'appName' => $application->name,
            'environment' => 'testing',
        ])
        ->assertExactJson([
            'feature_flag' => $featureFlag->slug,
            'value' => null,
            'active' => false
        ]);
});

it('fetches an active feature flag with no policy', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    $featureType = FeatureType::factory()->for($team)->create();
    $tags = Tag::factory(3)->for($team)->create();
    $application = Application::factory()->for($team)->create();
    $environment = Environment::factory()->for($team)->create();
    $featureFlag = FeatureFlag::factory()->for($team)->for($featureType)->hasAttached($tags)->create();
    $featureFlagStatus = FeatureFlagStatus::factory()->for($featureFlag)->for($application)->for($environment)->create([
        'status' => true
    ]);
    $featureFlag->statuses()->sync([$featureFlagStatus]);

    actingAs($user)
        ->post(route('api.feature-flags.show', ['slug' => $featureFlag->slug]), [
            'scopeType' => 'testing',
            'scope' => ['testing' => 'test'],
            'appName' => $application->name,
            'environment' => $environment->name,
        ])
        ->assertJsonFragment([
            'active' => true
        ])
        ->assertJsonStructure([
            'active',
            'feature_flag',
            'value'
        ]);
});

it('fetches an active feature flag', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    $featureType = FeatureType::factory()->for($team)->create();
    $tags = Tag::factory(3)->for($team)->create();
    $application = Application::factory()->for($team)->create();
    $environment = Environment::factory()->for($team)->create();
    $featureFlag = FeatureFlag::factory()->for($team)->for($featureType)->hasAttached($tags)->create();
    $policyDefinition = PolicyDefinition::from(PolicyDefinitionType::EXPRESSION, 'testing', PolicyDefinitionMatchOperator::EQUAL);
    $policy = Policy::factory()->for($team)->create([
        'definition' => PolicyDefinition::collect([
            $policyDefinition,
        ])
    ]);
    $featureFlagStatus = FeatureFlagStatus::factory()->for($featureFlag)->for($application)->for($environment)->create([
        'status' => true
    ]);
    $featureFlagStatus->policies()->syncWithPivotValues(
        [$policy],
        [
            'order' => 1,
            'values' => PolicyValue::collect([
                PolicyValue::from(
                    $policyDefinition,
                    Collection::make(['test']),
                    true
                )
            ])
        ]
    );
    $featureFlag->statuses()->sync([$featureFlagStatus]);

    actingAs($user)
        ->post(route('api.feature-flags.show', ['slug' => $featureFlag->slug]), [
            'scopeType' => 'testing',
            'scope' => ['testing' => 'test'],
            'appName' => $application->name,
            'environment' => $environment->name,
        ])
        ->assertJsonFragment([
            'active' => true
        ])
        ->assertJsonStructure([
            'active',
            'feature_flag',
            'value'
        ]);
});

it('fetches an active complex feature flag', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    $featureType = FeatureType::factory()->for($team)->create();
    $tags = Tag::factory(3)->for($team)->create();
    $application = Application::factory()->for($team)->create();
    $environment = Environment::factory()->for($team)->create();
    $featureFlag = FeatureFlag::factory()->for($team)->for($featureType)->hasAttached($tags)->create();
    $policyDefinitions = [];
    $policyDefinitions[] = PolicyDefinition::from(PolicyDefinitionType::EXPRESSION, 'testing', PolicyDefinitionMatchOperator::EQUAL);
    $policyDefinitions[] = PolicyDefinition::from(PolicyDefinitionType::OPERATOR, Boolean::OR->value);
    $policyDefinitions[] = PolicyDefinition::from(PolicyDefinitionType::EXPRESSION, 'test2', PolicyDefinitionMatchOperator::EQUAL);
    $policy = Policy::factory()->for($team)->create([
        'definition' => PolicyDefinition::collect($policyDefinitions)
    ]);
    $featureFlagStatus = FeatureFlagStatus::factory()->for($featureFlag)->for($application)->for($environment)->create([
        'status' => true
    ]);
    $featureFlagStatus->policies()->syncWithPivotValues(
        [$policy],
        [
            'order' => 1,
            'values' => PolicyValue::collect([
                PolicyValue::from(
                    $policyDefinitions[0],
                    Collection::make(['test']),
                ),
                PolicyValue::from(
                    $policyDefinitions[2],
                    Collection::make(['test2']),
                )
            ])
        ]
    );
    $featureFlag->statuses()->sync([$featureFlagStatus]);

    actingAs($user)
        ->post(route('api.feature-flags.show', ['slug' => $featureFlag->slug]), [
            'scopeType' => 'testing',
            'scope' => ['testing' => 'invalid', 'test2' => 'test2'],
            'appName' => $application->name,
            'environment' => $environment->name,
        ])
        ->assertJsonFragment([
            'active' => true
        ])
        ->assertJsonStructure([
            'active',
            'feature_flag',
            'value'
        ]);
});

it('fetches a complex feature flag as inactive with missing context value in OR', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    $featureType = FeatureType::factory()->for($team)->create();
    $tags = Tag::factory(3)->for($team)->create();
    $application = Application::factory()->for($team)->create();
    $environment = Environment::factory()->for($team)->create();
    $featureFlag = FeatureFlag::factory()->for($team)->for($featureType)->hasAttached($tags)->create();
    $policyDefinitions = [];
    $policyDefinitions[] = PolicyDefinition::from(PolicyDefinitionType::EXPRESSION, 'testing', PolicyDefinitionMatchOperator::EQUAL);
    $policyDefinitions[] = PolicyDefinition::from(PolicyDefinitionType::OPERATOR, Boolean::OR->value);
    $policyDefinitions[] = PolicyDefinition::from(PolicyDefinitionType::EXPRESSION, 'test2', PolicyDefinitionMatchOperator::EQUAL);
    $policy = Policy::factory()->for($team)->create([
        'definition' => PolicyDefinition::collect($policyDefinitions)
    ]);
    $featureFlagStatus = FeatureFlagStatus::factory()->for($featureFlag)->for($application)->for($environment)->create([
        'status' => true
    ]);
    $featureFlagStatus->policies()->syncWithPivotValues(
        [$policy],
        [
            'order' => 1,
            'values' => PolicyValue::collect([
                PolicyValue::from(
                    $policyDefinitions[0],
                    Collection::make(['test']),
                ),
                PolicyValue::from(
                    $policyDefinitions[2],
                    Collection::make(['test2']),
                )
            ])
        ]
    );
    $featureFlag->statuses()->sync([$featureFlagStatus]);

    actingAs($user)
        ->post(route('api.feature-flags.show', ['slug' => $featureFlag->slug]), [
            'scopeType' => 'testing',
            'scope' => ['test2' => 'test2'],
            'appName' => $application->name,
            'environment' => $environment->name,
        ])
        ->assertJsonFragment([
            'active' => true
        ])
        ->assertJsonStructure([
            'active',
            'feature_flag',
            'value'
        ]);
});

it('fetches a complex feature flag as inactive with missing context value in AND', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    $featureType = FeatureType::factory()->for($team)->create();
    $tags = Tag::factory(3)->for($team)->create();
    $application = Application::factory()->for($team)->create();
    $environment = Environment::factory()->for($team)->create();
    $featureFlag = FeatureFlag::factory()->for($team)->for($featureType)->hasAttached($tags)->create();
    $policyDefinitions = [];
    $policyDefinitions[] = PolicyDefinition::from(PolicyDefinitionType::EXPRESSION, 'testing', PolicyDefinitionMatchOperator::EQUAL);
    $policyDefinitions[] = PolicyDefinition::from(PolicyDefinitionType::OPERATOR, Boolean::AND->value);
    $policyDefinitions[] = PolicyDefinition::from(PolicyDefinitionType::EXPRESSION, 'test2', PolicyDefinitionMatchOperator::EQUAL);
    $policy = Policy::factory()->for($team)->create([
        'definition' => PolicyDefinition::collect($policyDefinitions)
    ]);
    $featureFlagStatus = FeatureFlagStatus::factory()->for($featureFlag)->for($application)->for($environment)->create([
        'status' => true
    ]);
    $featureFlagStatus->policies()->syncWithPivotValues(
        [$policy],
        [
            'order' => 1,
            'values' => PolicyValue::collect([
                PolicyValue::from(
                    $policyDefinitions[0],
                    Collection::make(['test']),
                ),
                PolicyValue::from(
                    $policyDefinitions[2],
                    Collection::make(['test2']),
                )
            ])
        ]
    );
    $featureFlag->statuses()->sync([$featureFlagStatus]);

    actingAs($user)
        ->post(route('api.feature-flags.show', ['slug' => $featureFlag->slug]), [
            'scopeType' => 'testing',
            'scope' => ['test2' => 'test2'],
            'appName' => $application->name,
            'environment' => $environment->name,
        ])
        ->assertJsonFragment([
            'active' => false
        ])
        ->assertJsonStructure([
            'active',
            'feature_flag',
            'value'
        ]);
});
