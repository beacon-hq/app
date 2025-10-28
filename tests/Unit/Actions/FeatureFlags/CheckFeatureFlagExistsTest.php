<?php

declare(strict_types=1);

use App\Actions\AppContext\SetAppContext;
use App\Actions\FeatureFlags\CheckFeatureFlagExists;
use App\Models\Application;
use App\Models\Environment;
use App\Models\FeatureFlag;
use App\Models\FeatureFlagStatus;
use App\Models\FeatureType;
use App\Values\FeatureFlag as FeatureFlagValue;
use App\Values\FeatureFlagContext;

beforeEach(function () {
    [$this->team, , $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

    // Create feature type
    $this->featureType = FeatureType::factory()->for($this->team)->create([
        'name' => 'Test Type',
        'description' => 'Type for testing',
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

    // Create feature flag that exists in app-a and env-a
    $this->existingFlag = FeatureFlag::factory()
        ->for($this->team)
        ->for($this->featureType, 'featureType')
        ->create([
            'name' => 'existing-flag',
            'description' => 'This flag exists in app-a/env-a',
        ]);

    FeatureFlagStatus::factory()->create([
        'feature_flag_id' => $this->existingFlag->id,
        'application_id' => $this->applicationA->id,
        'environment_id' => $this->environmentA->id,
        'status' => true,
    ]);

    // Create feature flag that exists only in app-b and env-b
    $this->otherAppEnvFlag = FeatureFlag::factory()
        ->for($this->team)
        ->for($this->featureType, 'featureType')
        ->create([
            'name' => 'other-app-env-flag',
            'description' => 'This flag exists only in app-b/env-b',
        ]);

    FeatureFlagStatus::factory()->create([
        'feature_flag_id' => $this->otherAppEnvFlag->id,
        'application_id' => $this->applicationB->id,
        'environment_id' => $this->environmentB->id,
        'status' => true,
    ]);

    $this->checkFeatureFlagExists = CheckFeatureFlagExists::make();
});

it('returns true when flag exists for the given app and environment', function () {
    $featureFlag = FeatureFlagValue::from(
        name: 'existing-flag',
        featureType: \App\Values\FeatureType::from(
            id: $this->featureType->id,
            name: $this->featureType->name,
            description: $this->featureType->description,
            icon: $this->featureType->icon ?? '',
            color: $this->featureType->color ?? '#000000',
            temporary: $this->featureType->temporary ?? false,
            isDefault: false
        ),
    );

    $context = FeatureFlagContext::from(
        scopeType: null,
        scope: [],
        appName: 'app-a',
        environment: 'env-a',
        sessionId: '',
        ip: '',
        userAgent: '',
        referrer: '',
        url: '',
        method: ''
    );

    $result = $this->checkFeatureFlagExists->handle($featureFlag, $context);

    expect($result)->toBeTrue();
});

it('returns false when flag does not exist for the given app and environment', function () {
    // Flag exists but not for this app and environment
    $featureFlag = FeatureFlagValue::from(
        name: 'other-app-env-flag',
        id: '',
        description: '',
        lastSeenAt: null,
        featureType: \App\Values\FeatureType::from(
            id: $this->featureType->id,
            name: $this->featureType->name,
            description: $this->featureType->description,
            icon: $this->featureType->icon ?? '',
            color: $this->featureType->color ?? '#000000',
            temporary: $this->featureType->temporary ?? false,
            isDefault: false
        ),
        tags: null,
        applications: null,
        statuses: null
    );

    $context = FeatureFlagContext::from(
        scopeType: null,
        scope: [],
        appName: 'app-a',
        environment: 'env-a',
        sessionId: '',
        ip: '',
        userAgent: '',
        referrer: '',
        url: '',
        method: ''
    );

    $result = $this->checkFeatureFlagExists->handle($featureFlag, $context);

    expect($result)->toBeFalse();
});

it('returns false when flag does not exist at all', function () {
    $featureFlag = FeatureFlagValue::from(
        name: 'non-existent-flag',
        id: '',
        description: '',
        lastSeenAt: null,
        featureType: \App\Values\FeatureType::from(
            id: $this->featureType->id,
            name: $this->featureType->name,
            description: $this->featureType->description,
            icon: $this->featureType->icon ?? '',
            color: $this->featureType->color ?? '#000000',
            temporary: $this->featureType->temporary ?? false,
            isDefault: false
        ),
        tags: null,
        applications: null,
        statuses: null
    );

    $context = FeatureFlagContext::from(
        scopeType: null,
        scope: [],
        appName: 'app-a',
        environment: 'env-a',
        sessionId: '',
        ip: '',
        userAgent: '',
        referrer: '',
        url: '',
        method: ''
    );

    $result = $this->checkFeatureFlagExists->handle($featureFlag, $context);

    expect($result)->toBeFalse();
});

it('returns true when flag exists for the correct environment but different app', function () {
    $featureFlag = FeatureFlagValue::from(
        name: 'other-app-env-flag',
        id: '',
        description: '',
        lastSeenAt: null,
        featureType: \App\Values\FeatureType::from(
            id: $this->featureType->id,
            name: $this->featureType->name,
            description: $this->featureType->description,
            icon: $this->featureType->icon ?? '',
            color: $this->featureType->color ?? '#000000',
            temporary: $this->featureType->temporary ?? false,
            isDefault: false
        ),
        tags: null,
        applications: null,
        statuses: null
    );

    $context = FeatureFlagContext::from(
        scopeType: null,
        scope: [],
        appName: 'app-b',
        environment: 'env-b',
        sessionId: '',
        ip: '',
        userAgent: '',
        referrer: '',
        url: '',
        method: ''
    );

    $result = $this->checkFeatureFlagExists->handle($featureFlag, $context);

    expect($result)->toBeTrue();
});
