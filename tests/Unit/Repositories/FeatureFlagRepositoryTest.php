<?php

declare(strict_types=1);

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
