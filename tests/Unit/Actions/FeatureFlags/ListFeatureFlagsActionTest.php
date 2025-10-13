<?php

declare(strict_types=1);

use App\Actions\FeatureFlags\ListFeatureFlags;
use App\Models\Application;
use App\Models\Environment;
use App\Models\FeatureFlag;
use App\Models\FeatureFlagStatus;
use App\Models\FeatureType;
use App\Models\Tag;
use Tests\Unit\Actions\AppContext\SetAppContext;

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

    // Create feature flags with different characteristics for testing filters and sorting

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

    $this->listFeatureFlagsAction = ListFeatureFlags::make();
});

it('returns all feature flags sorted by name by default', function () {
    $result = $this->listFeatureFlagsAction->handle();

    expect($result)->toHaveCount(3)
        ->and($result[0]->id)->toBe($this->flagA->id)
        ->and($result[1]->id)->toBe($this->flagB->id)
        ->and($result[2]->id)->toBe($this->flagC->id)
        ->and($result[0]->name)->toBe('flag-a')
        ->and($result[1]->name)->toBe('flag-b')
        ->and($result[2]->name)->toBe('flag-c');
});

it('can filter feature flags by name', function () {
    $result = $this->listFeatureFlagsAction->handle(null, 20, ['name' => 'flag-a']);

    expect($result)->toHaveCount(1)
        ->and($result[0]->id)->toBe($this->flagA->id)
        ->and($result[0]->name)->toBe('flag-a');
});

it('can filter feature flags by tag', function () {
    $result = $this->listFeatureFlagsAction->handle(null, 20, ['tag' => $this->tagB->id]);

    expect($result)->toHaveCount(2)
        ->and($result->pluck('id'))->toContain($this->flagB->id)
        ->and($result->pluck('id'))->toContain($this->flagC->id)
        ->and($result->pluck('id'))->not->toContain($this->flagA->id);
});

it('can filter feature flags by feature type', function () {
    $result = $this->listFeatureFlagsAction->handle(null, 20, ['feature_type' => $this->featureTypeA->id]);

    expect($result)->toHaveCount(2)
        ->and($result->pluck('id'))->toContain($this->flagA->id)
        ->and($result->pluck('id'))->toContain($this->flagC->id)
        ->and($result->pluck('id'))->not->toContain($this->flagB->id);
});

it('can filter feature flags by application', function () {
    $result = $this->listFeatureFlagsAction->handle(null, 20, ['application' => $this->applicationB->id]);

    expect($result)->toHaveCount(2)
        ->and($result->pluck('id'))->toContain($this->flagB->id)
        ->and($result->pluck('id'))->toContain($this->flagC->id)
        ->and($result->pluck('id'))->not->toContain($this->flagA->id);
});

it('can filter feature flags by environment', function () {
    $result = $this->listFeatureFlagsAction->handle(null, 20, ['environment' => $this->environmentB->id]);

    expect($result)->toHaveCount(1)
        ->and($result[0]->id)->toBe($this->flagB->id);
});

it('can filter feature flags by status', function () {
    $result = $this->listFeatureFlagsAction->handle(null, 20, ['status' => ['enabled']]);

    expect($result)->toHaveCount(2)
        ->and($result->pluck('id'))->toContain($this->flagA->id)
        ->and($result->pluck('id'))->toContain($this->flagB->id)
        ->and($result->pluck('id'))->not->toContain($this->flagC->id);

    $result = $this->listFeatureFlagsAction->handle(null, 20, ['status' => ['disabled']]);

    expect($result)->toHaveCount(1)
        ->and($result[0]->id)->toBe($this->flagC->id);
});

it('can combine multiple filters', function () {
    $result = $this->listFeatureFlagsAction->handle(null, 20, [
        'feature_type' => $this->featureTypeA->id,
        'tag' => $this->tagA->id,
    ]);

    expect($result)->toHaveCount(2)
        ->and($result->pluck('id'))->toContain($this->flagA->id)
        ->and($result->pluck('id'))->toContain($this->flagC->id)
        ->and($result->pluck('id'))->not->toContain($this->flagB->id);

    $result = $this->listFeatureFlagsAction->handle(null, 20, [
        'feature_type' => $this->featureTypeA->id,
        'application' => $this->applicationB->id,
    ]);

    expect($result)->toHaveCount(1)
        ->and($result[0]->id)->toBe($this->flagC->id);
});

it('can sort feature flags in ascending order', function () {
    // Sort by name ascending (default)
    $result = $this->listFeatureFlagsAction->handle(null, 20, [], ['name' => 'asc']);

    expect($result)->toHaveCount(3)
        ->and($result[0]->name)->toBe('flag-a')
        ->and($result[1]->name)->toBe('flag-b')
        ->and($result[2]->name)->toBe('flag-c');
});

it('can sort feature flags in descending order', function () {
    // Sort by name descending
    $result = $this->listFeatureFlagsAction->handle(null, 20, [], ['name' => 'desc']);

    expect($result)->toHaveCount(3)
        ->and($result[0]->name)->toBe('flag-c')
        ->and($result[1]->name)->toBe('flag-b')
        ->and($result[2]->name)->toBe('flag-a');
});

it('can paginate feature flags', function () {
    // First page (2 per page)
    $result = $this->listFeatureFlagsAction->handle(1, 2);

    expect($result)->toHaveCount(2)
        ->and($result[0]->name)->toBe('flag-a')
        ->and($result[1]->name)->toBe('flag-b');

    // Second page (2 per page)
    $result = $this->listFeatureFlagsAction->handle(2, 2);

    expect($result)->toHaveCount(1)
        ->and($result[0]->name)->toBe('flag-c');
});
