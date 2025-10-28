<?php

declare(strict_types=1);

use App\Actions\AppContext\SetAppContext;
use App\Actions\FeatureFlags\CountFeatureFlags;
use App\Models\Application;
use App\Models\Environment;
use App\Models\FeatureFlag;
use App\Models\FeatureFlagStatus;
use App\Models\FeatureType;
use App\Models\Tag;

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

    // Create feature flags with different characteristics for testing filters

    // Flag 1: Type A, Tagged with A, Enabled in App A + Env A
    $this->flagA = FeatureFlag::factory()
        ->for($this->team)
        ->for($this->featureTypeA, 'featureType')
        ->create([
            'name' => 'flag-a',
            'description' => 'Description for Flag A',
            'status' => true,
        ]);

    $this->flagA->tags()->attach($this->tagA->id);

    FeatureFlagStatus::factory()->create([
        'feature_flag_id' => $this->flagA->id,
        'application_id' => $this->applicationA->id,
        'environment_id' => $this->environmentA->id,
        'status' => true,
    ]);

    // Flag 2: Type B, Tagged with B, Disabled in App A + Env A, Enabled in App B + Env B
    $this->flagB = FeatureFlag::factory()
        ->for($this->team)
        ->for($this->featureTypeB, 'featureType')
        ->create([
            'name' => 'flag-b',
            'description' => 'Description for Flag B',
            'status' => true,
        ]);

    $this->flagB->tags()->attach($this->tagB->id);

    FeatureFlagStatus::factory()->create([
        'feature_flag_id' => $this->flagB->id,
        'application_id' => $this->applicationA->id,
        'environment_id' => $this->environmentA->id,
        'status' => false,
    ]);

    FeatureFlagStatus::factory()->create([
        'feature_flag_id' => $this->flagB->id,
        'application_id' => $this->applicationB->id,
        'environment_id' => $this->environmentB->id,
        'status' => true,
    ]);

    // Flag 3: Type A, Tagged with A and B, Enabled in App B + Env A
    $this->flagC = FeatureFlag::factory()
        ->for($this->team)
        ->for($this->featureTypeA, 'featureType')
        ->create([
            'name' => 'flag-c',
            'description' => 'Description for Flag C',
            'status' => false,
        ]);

    $this->flagC->tags()->attach([$this->tagA->id, $this->tagB->id]);

    FeatureFlagStatus::factory()->create([
        'feature_flag_id' => $this->flagC->id,
        'application_id' => $this->applicationB->id,
        'environment_id' => $this->environmentA->id,
        'status' => true,
    ]);

    $this->countFeatureFlagsAction = CountFeatureFlags::make();
});

it('counts all feature flags when no filters are applied', function () {
    $count = $this->countFeatureFlagsAction->handle();

    expect($count)->toBe(3);
});

it('counts feature flags filtered by name', function () {
    $count = $this->countFeatureFlagsAction->handle(['name' => 'flag-a']);

    expect($count)->toBe(1);

    $count = $this->countFeatureFlagsAction->handle(['name' => 'flag']);

    expect($count)->toBe(3); // All flags have 'flag' in their name
});

it('counts feature flags filtered by tag', function () {
    $count = $this->countFeatureFlagsAction->handle(['tag' => $this->tagA->id]);

    expect($count)->toBe(2); // flagA and flagC have tagA

    $count = $this->countFeatureFlagsAction->handle(['tag' => $this->tagB->id]);

    expect($count)->toBe(2); // flagB and flagC have tagB
});

it('counts feature flags filtered by feature type', function () {
    $count = $this->countFeatureFlagsAction->handle(['featureType' => $this->featureTypeA->id]);

    expect($count)->toBe(2); // flagA and flagC are featureTypeA

    $count = $this->countFeatureFlagsAction->handle(['featureType' => $this->featureTypeB->id]);

    expect($count)->toBe(1); // only flagB is featureTypeB
});

it('counts feature flags filtered by application', function () {
    $count = $this->countFeatureFlagsAction->handle(['application' => $this->applicationA->name]);

    expect($count)->toBe(2); // flagA and flagB have applicationA

    $count = $this->countFeatureFlagsAction->handle(['application' => $this->applicationB->name]);

    expect($count)->toBe(2); // flagB and flagC have applicationB
});

it('counts feature flags filtered by environment', function () {
    $count = $this->countFeatureFlagsAction->handle(['environment' => $this->environmentA->name]);

    expect($count)->toBe(3); // All flags have environmentA

    $count = $this->countFeatureFlagsAction->handle(['environment' => $this->environmentB->name]);

    expect($count)->toBe(1); // Only flagB has environmentB
});

it('counts feature flags filtered by status', function () {
    $count = $this->countFeatureFlagsAction->handle(['status' => 'active']);

    expect($count)->toBe(2);

    $count = $this->countFeatureFlagsAction->handle(['status' => 'inactive']);

    expect($count)->toBe(1);
});

it('counts feature flags with multiple filters combined', function () {
    $count = $this->countFeatureFlagsAction->handle([
        'featureType' => $this->featureTypeA->id,
        'tag' => $this->tagA->id,
    ]);

    expect($count)->toBe(2); // flagA and flagC are featureTypeA and have tagA

    $count = $this->countFeatureFlagsAction->handle([
        'featureType' => $this->featureTypeA->id,
        'application' => $this->applicationB->name,
    ]);

    expect($count)->toBe(1); // Only flagC is featureTypeA and has applicationB

    $count = $this->countFeatureFlagsAction->handle([
        'tag' => $this->tagB->id,
        'status' => false,
    ]);

    expect($count)->toBe(1); // Only flagB has tagB and a false status
});
