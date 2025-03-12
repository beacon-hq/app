<?php

declare(strict_types=1);

use App\Models\FeatureFlag;
use App\Models\FeatureType;
use App\Models\Tag;
use App\Models\Team;
use App\Repositories\FeatureFlagRepository;

it('counts the number of feature flags', function () {
    $team = Team::factory()->create();
    $featureType = FeatureType::factory()->for($team)->create();
    $tags = Tag::factory(3)->for($team)->create();
    FeatureFlag::factory(18)->active()->for($team)->for($featureType)->hasAttached($tags)->create();

    $featureFlagRepository = resolve(FeatureFlagRepository::class);
    expect($featureFlagRepository->count())->toBe(18);
});

it('counts the number of feature flags with filter', function () {
    $team = Team::factory()->create();
    $featureType = FeatureType::factory()->for($team)->create();
    $tags = Tag::factory(3)->for($team)->create();
    FeatureFlag::factory(18)->active()->for($team)->for($featureType)->hasAttached($tags)->create();

    $expected = FeatureFlag::where('name', 'LIKE', '%et%')->count();

    $featureFlagRepository = resolve(FeatureFlagRepository::class);
    expect($featureFlagRepository->count(['name' => ['et']]))->toBe($expected);
});
