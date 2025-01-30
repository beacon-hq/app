<?php

declare(strict_types=1);

use App\Models\FeatureFlag;
use App\Models\FeatureType;
use App\Models\Tag;
use App\Models\Team;
use App\Models\User;
use App\Values\Collections\TagCollection;
use Inertia\Testing\AssertableInertia as Assert;

it('it lists feature flags', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    $featureType = FeatureType::factory()->for($team)->create();
    $tags = Tag::factory(3)->for($team)->create();
    FeatureFlag::factory(5)->for($team)->for($featureType)->hasAttached($tags)->create();

    $this->actingAs($user)->get('/feature-flags')
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('FeatureFlags/Index')
                ->has('featureFlags', 5)
                ->has('featureTypes', 1)
                ->has('tags', 3)
        );
});

it('it creates a feature flag', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    $featureType = FeatureType::factory()->for($team)->create();
    $tags = Tag::factory(3)->for($team)->create();

    $this->actingAs($user)->post('/feature-flags', [
        'name' => 'Test Feature Flag',
        'description' => 'test description',
        'feature_type' => collect($featureType->toArray())->except('team_id')->toArray(),
        'tags' => $tags->map(fn ($tag) => collect($tag)->except('team_id')->toArray())->toArray(),
    ])->assertRedirect('/feature-flags');

    $this->assertDatabaseHas('feature_flags', [
        'name' => 'Test Feature Flag',
        'slug' => 'test-feature-flag',
        'description' => 'test description',
        'feature_type_id' => $featureType->id,
        'team_id' => $team->id,
    ]);

    $tags->each(function ($tag) {
        $this->assertDatabaseHas('feature_flag_tag', [
            'feature_flag_id' => FeatureFlag::whereName('Test Feature Flag')->first()->id,
            'tag_id' => $tag->id,
        ]);
    });
});

it('it fails validation with missing required fields on create', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();

    $this->actingAs($user)->post('/feature-flags', [])->assertInvalid(['name', 'featureType']);
});

it('shows the edit pages', function (string $route) {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    $featureType = FeatureType::factory()->for($team)->create();
    $tags = Tag::factory(3)->for($team)->create();
    $featureFlag = FeatureFlag::factory()->for($team)->for($featureType)->hasAttached($tags)->create();

    $this->actingAs($user)->get(route($route, ['slug' => $featureFlag->slug]))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('FeatureFlags/Edit')
                ->has('featureFlag')
                ->has('featureTypes', 1)
                ->has('tags', 3)
        );
})->with(['feature-flags.edit', 'feature-flags.edit.policy', 'feature-flags.edit.overview']);

it('updates a feature flag', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    $featureTypes = FeatureType::factory(3)->for($team)->create();
    $tags = Tag::factory(2)->for($team)->create();
    $featureFlag = FeatureFlag::factory()->for($team)->for($featureTypes->first())->hasAttached($tags->first())->create();

    $this
        ->actingAs($user)
        ->from(route('feature-flags.index'))
        ->put(route('feature-flags.update', ['slug' => $featureFlag->slug]), [
            'name' => 'ignored name',
            'description' => 'updated description',
            'feature_type' => \App\Values\FeatureType::from($featureTypes->last())->toArray(),
            'tags' => TagCollection::make($tags->toArray())->toArray(),
        ])->assertRedirect(route('feature-flags.edit.overview', ['slug' => $featureFlag->slug]));

    $this->assertDatabaseHas('feature_flags', [
        'name' => $featureFlag->name,
        'slug' => $featureFlag->slug,
        'description' => 'updated description',
        'feature_type_id' => $featureTypes->last()->id,
    ]);

    $this->assertDatabaseHas('feature_flag_tag', [
        'feature_flag_id' => $featureFlag->id,
        'tag_id' => $tags->first()->id,
    ]);
    $this->assertDatabaseHas('feature_flag_tag', [
        'feature_flag_id' => $featureFlag->id,
        'tag_id' => $tags->last()->id,
    ]);
});

it('it fails validation', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    $featureTypes = FeatureType::factory(3)->for($team)->create();
    $tags = Tag::factory(2)->for($team)->create();
    $featureFlag = FeatureFlag::factory()->for($team)->for($featureTypes->first())->hasAttached($tags->first())->create();

    $this
        ->actingAs($user)
        ->from(route('feature-flags.index'))
        ->put(route('feature-flags.update', ['slug' => $featureFlag->slug]), [])
        ->assertInvalid(['featureType']);
});
