<?php

declare(strict_types=1);

use App\Actions\AppContext\SetAppContext;
use App\Actions\FeatureFlags\UpdateFeatureFlag;
use App\Models\Application;
use App\Models\Environment;
use App\Models\FeatureFlag;
use App\Models\FeatureFlagStatus;
use App\Models\FeatureType;
use App\Models\Tag;
use App\Values\Application as ApplicationValue;
use App\Values\Collections\FeatureFlagStatusCollection;
use App\Values\Collections\TagCollection;
use App\Values\Environment as EnvironmentValue;
use App\Values\FeatureFlag as FeatureFlagValue;
use App\Values\FeatureFlagStatus as FeatureFlagStatusValue;
use App\Values\FeatureType as FeatureTypeValue;
use App\Values\Tag as TagValue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    [$this->team, , $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

    // Create initial feature type
    $this->initialFeatureType = FeatureType::factory()->for($this->team)->create([
        'name' => 'Initial Type',
        'description' => 'Initial feature type',
        'color' => '#ff0000',
        'icon' => 'Flag',
    ]);

    // Create new feature type for update
    $this->newFeatureType = FeatureType::factory()->for($this->team)->create([
        'name' => 'Updated Type',
        'description' => 'Updated feature type',
        'color' => '#00ff00',
        'icon' => 'Rocket',
    ]);

    // Create initial application and environment
    $this->initialApplication = Application::factory()->for($this->team)->create([
        'name' => 'initial-app',
        'display_name' => 'Initial App',
    ]);

    $this->initialEnvironment = Environment::factory()->for($this->team)->create([
        'name' => 'initial-env',
        'description' => 'Initial Environment',
    ]);

    // Create new application and environment for update
    $this->newApplication = Application::factory()->for($this->team)->create([
        'name' => 'new-app',
        'display_name' => 'New App',
    ]);

    $this->newEnvironment = Environment::factory()->for($this->team)->create([
        'name' => 'new-env',
        'description' => 'New Environment',
    ]);

    // Create initial tags
    $this->initialTags = collect();
    for ($i = 0; $i < 2; $i++) {
        $this->initialTags->push(Tag::factory()->for($this->team)->create([
            'name' => "initial-tag-{$i}",
        ]));
    }

    // Create new tags for update
    $this->newTags = collect();
    for ($i = 0; $i < 3; $i++) {
        $this->newTags->push(Tag::factory()->for($this->team)->create([
            'name' => "new-tag-{$i}",
        ]));
    }

    // Create initial feature flag
    $this->featureFlag = FeatureFlag::factory()
        ->for($this->team)
        ->for($this->initialFeatureType)
        ->create([
            'name' => 'initial-flag',
            'description' => 'Initial feature flag description',
        ]);

    // Attach initial tags
    $this->featureFlag->tags()->attach($this->initialTags->pluck('id'));

    // Create initial feature flag status
    $this->initialStatus = FeatureFlagStatus::factory()->create([
        'feature_flag_id' => $this->featureFlag->id,
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
            id: $this->featureFlag->id,
            name: 'updated-flag',
            description: 'Updated feature flag description',
            lastSeenAt: null,
            featureType: $newFeatureType,
            tags: $newTags,
            applications: null,
            statuses: FeatureFlagStatusCollection::make([$newFeatureFlagStatus]),
        );

        $result = $this->updateFeatureFlagAction->handle($updatedFeatureFlag);

        $this->featureFlag->refresh();

        expect($this->featureFlag->name)
            ->toBe('initial-flag')
            ->and($this->featureFlag->description)
            ->toBe('Updated feature flag description')
            ->and($this->featureFlag->feature_type_id)
            ->toBe($this->newFeatureType->id)
            ->and($result->name)
            ->toBe('initial-flag')
            ->and($result->description)
            ->toBe('Updated feature flag description')
            ->and($result->featureType->id)
            ->toBe($this->newFeatureType->id)
            ->and($this->featureFlag->tags)
            ->toHaveCount(3)
            ->and($this->featureFlag->tags->pluck('id'))
            ->not
            ->toContain($this->initialTags->first()->id)
            ->and($this->featureFlag->tags->pluck('id'))
            ->toContain($this->newTags->first()->id)
            ->and($result->tags)
            ->toHaveCount(3)
            ->and($this->featureFlag->statuses)
            ->toHaveCount(1)
            ->and($this->featureFlag->statuses->first()->application_id)
            ->toBe($this->newApplication->id)
            ->and($this->featureFlag->statuses->first()->environment_id)
            ->toBe($this->newEnvironment->id)
            ->and($this->featureFlag->statuses->first()->status)
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
            ->where('feature_flag_id', $this->featureFlag->id)
            ->first();

        expect($oldStatus)->toBeNull();
    } finally {
        DB::rollBack();
    }
});

it('throws an exception when feature flag does not exist', function () {
    DB::beginTransaction();

    try {
        // Create a feature flag value object with non-existent ID
        $nonExistentFeatureFlag = FeatureFlagValue::from(
            id: 'non-existent-id',
            name: 'updated-flag',
            description: 'Updated feature flag description',
            lastSeenAt: null,
            featureType: FeatureTypeValue::from(
                id: $this->initialFeatureType->id,
                name: $this->initialFeatureType->name,
                description: $this->initialFeatureType->description,
                icon: $this->initialFeatureType->icon,
                color: $this->initialFeatureType->color,
                temporary: $this->initialFeatureType->temporary,
                isDefault: false
            ),
            tags: TagCollection::make([]),
            applications: null,
            statuses: FeatureFlagStatusCollection::make([])
        );

        // Call the action
        $this->updateFeatureFlagAction->handle($nonExistentFeatureFlag);
    } finally {
        DB::rollBack();
    }
})->throws(ModelNotFoundException::class);
