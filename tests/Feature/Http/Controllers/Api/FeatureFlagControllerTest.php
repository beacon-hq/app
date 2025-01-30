<?php

declare(strict_types=1);

use App\Models\Application;
use App\Models\Environment;
use App\Models\FeatureFlag;
use App\Models\FeatureFlagStatus;
use App\Models\FeatureType;
use App\Models\Tag;
use App\Models\Team;
use App\Models\User;
use function Pest\Laravel\actingAs;

it('fetches a missing feature flag as inactive', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    $featureType = FeatureType::factory()->for($team)->create();
    $tags = Tag::factory(3)->for($team)->create();

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

it('fetches a feature flag without matching application', function () {
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

it('fetches a feature flag without matching environemnt', function () {
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

it('fetches an active feature flag', function () {
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
