<?php

declare(strict_types=1);

use App\Actions\FeatureFlags\FindFeatureFlag;
use App\Models\Application;
use App\Models\Environment;
use App\Models\FeatureFlag;
use App\Models\FeatureFlagStatus;
use App\Models\FeatureType;
use App\Models\Policy;
use App\Models\Tag;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\Unit\Actions\AppContext\SetAppContext;

beforeEach(function () {
    [$this->team, , $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

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

    $this->featureFlag = FeatureFlag::factory()
        ->for($this->team)
        ->for($this->featureType)
        ->create([
            'name' => 'test-flag',
            'description' => 'Test feature flag',
        ]);

    $this->featureFlag->tags()->attach($this->tags->pluck('id'));

    $this->featureFlagStatus = FeatureFlagStatus::factory()->create([
        'feature_flag_id' => $this->featureFlag->id,
        'application_id' => $this->application->id,
        'environment_id' => $this->environment->id,
        'status' => true,
    ]);

    $this->featureFlag->statuses()->attach($this->featureFlagStatus->id);

    $this->findFeatureFlagAction = FindFeatureFlag::make();
});

it('can find a feature flag by ID with all relations', function () {
    $result = $this->findFeatureFlagAction->handle($this->featureFlag->id);

    expect($result->id)->toBe($this->featureFlag->id)
        ->and($result->name)
        ->toBe('test-flag')
        ->and($result->description)
        ->toBe('Test feature flag')
        ->and($result->featureType->id)
        ->toBe($this->featureType->id)
        ->and($result->featureType->name)
        ->toBe('Test Type')
        ->and($result->featureType->color)
        ->toBe('#ff0000')
        ->and($result->featureType->icon)
        ->toBe('Flag')
        ->and($result->tags)
        ->toHaveCount(2)
        ->and($result->tags->pluck('id'))
        ->toContain($this->tags->first()->id)
        ->and($result->tags->pluck('id'))
        ->toContain($this->tags->last()->id)
        ->and($result->statuses)
        ->toHaveCount(1)
        ->and($result->statuses->first()->application->id)
        ->toBe($this->application->id)
        ->and($result->statuses->first()->environment->id)
        ->toBe($this->environment->id)
        ->and($result->statuses->first()->status)
        ->toBeTrue();
});

it('throws an exception when feature flag does not exist', function () {
    $this->findFeatureFlagAction->handle('non-existent-id');
})->throws(ModelNotFoundException::class);
