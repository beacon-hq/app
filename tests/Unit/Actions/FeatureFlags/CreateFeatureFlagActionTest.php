<?php

declare(strict_types=1);

use App\Actions\FeatureFlags\CreateFeatureFlag;
use App\Models\Application;
use App\Models\Environment;
use App\Models\FeatureFlag;
use App\Models\FeatureType;
use App\Models\Policy;
use App\Models\Tag;
use App\Values\Collections\TagCollection;
use App\Values\FeatureFlag as FeatureFlagValue;
use App\Values\FeatureType as FeatureTypeValue;
use Tests\Unit\Actions\AppContext\SetAppContext;

beforeEach(function () {
    [$this->team, , $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

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

    $this->createFeatureFlagAction = CreateFeatureFlag::make();
});

it('can create a feature flag with all relationships', function () {
    $tags = TagCollection::make($this->tags->map(fn ($tag) => \App\Values\Tag::from(
        id: $tag->id,
        name: $tag->name,
        color: $tag->color
    )));

    $featureType = FeatureTypeValue::from(
        id: $this->featureType->id,
        name: $this->featureType->name,
        description: $this->featureType->description,
        icon: $this->featureType->icon,
        color: $this->featureType->color,
        temporary: $this->featureType->temporary,
        isDefault: false
    );

    $featureFlag = FeatureFlagValue::from(
        id: '',
        name: 'new-test-flag',
        description: 'New test feature flag',
        lastSeenAt: null,
        featureType: $featureType,
        tags: $tags,
        applications: null,
        statuses: null,
    );

    $result = $this->createFeatureFlagAction->handle($featureFlag);

    $model = FeatureFlag::where('name', 'new-test-flag')->first();

    expect($model)
        ->not
        ->toBeNull()
        ->and($model->name)
        ->toBe('new-test-flag')
        ->and($model->description)
        ->toBe('New test feature flag')
        ->and($model->feature_type_id)
        ->toBe($this->featureType->id)
        ->and($result->name)
        ->toBe('new-test-flag')
        ->and($result->description)
        ->toBe('New test feature flag')
        ->and($result->featureType->id)
        ->toBe($this->featureType->id)
        ->and($model->tags)
        ->toHaveCount(2)
        ->and($model->tags->pluck('id'))
        ->toContain($this->tags->first()->id)
        ->and($model->tags->pluck('id'))
        ->toContain($this->tags->last()->id)
        ->and($result->tags)
        ->toHaveCount(2)
        ->and($model->statuses)
        ->toHaveCount(0)
        ->and($result->statuses)
        ->toHaveCount(0);
});
