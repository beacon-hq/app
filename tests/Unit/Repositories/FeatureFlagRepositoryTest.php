<?php

declare(strict_types=1);

use App\Enums\RolloutStrategy;
use App\Enums\VariantStrategy;
use App\Models\Application;
use App\Models\Environment;
use App\Models\FeatureFlag;
use App\Models\FeatureFlagStatus;
use App\Models\FeatureType;
use App\Models\Organization;
use App\Models\Tag;
use App\Models\Team;
use App\Repositories\FeatureFlagRepository;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

it('counts the number of feature flags', function () {
    $team = Team::factory()->for(Organization::factory()->create())->create();
    App::context(team: $team, organization: $team->organization);

    $featureType = FeatureType::factory()->for($team)->create();
    $tags = Tag::factory(3)->for($team)->create();
    FeatureFlag::factory(18)->active()->for($team)->for($featureType)->hasAttached($tags)->create();

    $featureFlagRepository = resolve(FeatureFlagRepository::class);
    expect($featureFlagRepository->count())->toBe(18);
});

it('counts the number of feature flags with filter', function () {
    $team = Team::factory()->for(Organization::factory()->create())->create();
    App::context(team: $team, organization: $team->organization);

    $featureType = FeatureType::factory()->for($team)->create();
    $tags = Tag::factory(3)->for($team)->create();
    FeatureFlag::factory(18)->active()->for($team)->for($featureType)->hasAttached($tags)->create();

    $expected = FeatureFlag::where('name', 'LIKE', '%et%')->count();

    $featureFlagRepository = resolve(FeatureFlagRepository::class);
    expect($featureFlagRepository->count(['name' => ['et']]))->toBe($expected);
});

it('returns activity logs for both feature flag and feature flag status', function () {
    Config::set('activitylog.enabled', true);
    $team = Team::factory()->for(Organization::factory()->create())->create();
    App::context(team: $team, organization: $team->organization);

    $featureType = FeatureType::factory()->for($team)->create();
    $application = Application::factory()->for($team)->create();
    $environment = Environment::factory()->for($team)->create();

    $featureFlag = FeatureFlag::factory()->for($team)->for($featureType)->create([
        'name' => 'test-flag',
        'description' => 'Initial description',
    ]);

    $featureFlagStatus = FeatureFlagStatus::factory()
        ->for($featureFlag)
        ->for($application)
        ->for($environment)
        ->create([
            'status' => true,
        ]);

    $featureFlag->update(['description' => 'Updated description']);
    $featureFlagStatus->update(['status' => false]);

    $featureFlagRepository = resolve(FeatureFlagRepository::class);
    $activities = $featureFlagRepository->activityLog($featureFlag->id);

    expect($activities)->toHaveCount(4)
        ->and($activities->pluck('log_name')->unique()->sort()->values()->toArray())
        ->toEqual(['feature_flag', 'feature_flag_status']);
});

it('filters out statuses with null application or environment when updating', function () {
    $team = Team::factory()->for(Organization::factory()->create())->create();
    App::context(team: $team, organization: $team->organization);

    $featureType = FeatureType::factory()->for($team)->create();
    $application = Application::factory()->for($team)->create();
    $environment = Environment::factory()->for($team)->create();

    $featureFlag = FeatureFlag::factory()->for($team)->for($featureType)->create();

    // Create a FeatureFlagValue with mixed statuses - some valid, some with null application/environment
    $featureFlagValue = \App\Values\FeatureFlag::from([
        'id' => $featureFlag->id,
        'name' => $featureFlag->name,
        'description' => $featureFlag->description,
        'status' => $featureFlag->status,
        'featureType' => \App\Values\FeatureType::from($featureType),
        'tags' => collect([]),
        'statuses' => collect([
            // Valid status
            \App\Values\FeatureFlagStatus::from([
                'id' => null,
                'application' => \App\Values\Application::from($application),
                'environment' => \App\Values\Environment::from($environment),
                'status' => true,
                'definition' => [],
                'rolloutPercentage' => 100,
                'rolloutContext' => [],
                'rolloutStrategy' => RolloutStrategy::RANDOM,
                'variants' => [],
                'variantStrategy' => VariantStrategy::RANDOM,
                'variantContext' => [],
            ]),
            // Invalid status with null application
            \App\Values\FeatureFlagStatus::from([
                'id' => null,
                'application' => null,
                'environment' => \App\Values\Environment::from($environment),
                'status' => false,
                'definition' => [],
                'rolloutPercentage' => 50,
                'rolloutContext' => [],
                'rolloutStrategy' => RolloutStrategy::RANDOM,
                'variants' => [],
                'variantStrategy' => VariantStrategy::RANDOM,
                'variantContext' => [],
            ]),
            // Invalid status with null environment
            \App\Values\FeatureFlagStatus::from([
                'id' => null,
                'application' => \App\Values\Application::from($application),
                'environment' => null,
                'status' => true,
                'definition' => [],
                'rolloutPercentage' => 75,
                'rolloutContext' => [],
                'rolloutStrategy' => RolloutStrategy::RANDOM,
                'variants' => [],
                'variantStrategy' => VariantStrategy::RANDOM,
                'variantContext' => [],
            ]),
        ]),
    ]);

    $featureFlagRepository = resolve(FeatureFlagRepository::class);

    // This should not throw an error and should only create the valid status
    $updatedFeatureFlag = $featureFlagRepository->update($featureFlagValue);

    // Verify that only the valid status was created
    expect($updatedFeatureFlag->statuses)->toHaveCount(1)
        ->and($updatedFeatureFlag->statuses->first()->application_id)->toBe($application->id)
        ->and($updatedFeatureFlag->statuses->first()->environment_id)->toBe($environment->id)
        ->and($updatedFeatureFlag->statuses->first()->status)->toBe(true);
});
