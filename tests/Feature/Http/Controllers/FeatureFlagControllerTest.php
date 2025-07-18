<?php

declare(strict_types=1);

use App\Models\FeatureFlag;
use App\Models\FeatureType;
use App\Models\Tag;
use App\Models\Team;
use App\Models\User;
use App\Values\Collections\TagCollection;
use Inertia\Testing\AssertableInertia as Assert;

it('lists feature flags', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    $featureType = FeatureType::factory()->for($team)->create();
    $tags = Tag::factory(3)->for($team)->create();
    FeatureFlag::factory(5)->for($team)->for($featureType)->hasAttached($tags)->create();

    $this->actingAs($user)->get(route('feature-flags.index'))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('FeatureFlags/Index')
                ->has('featureFlags', 5)
                ->has('featureTypes', 5)
                ->has('tags', 3)
        );
});

it('creates a feature flag', function () {
    [$team, $user] = createOwner();
    $featureType = FeatureType::factory()->for($team)->create();
    $tags = Tag::factory(3)->for($team)->create();

    $this
        ->actingAs($user)
        ->post('/feature-flags', [
            'name' => 'Test Feature Flag',
            'description' => 'test description',
            'feature_type' => collect($featureType->toArray())->except('team_id')->toArray(),
            'tags' => $tags->map(fn ($tag) => collect($tag)->except('team_id')->toArray())->toArray(),
        ])
        ->assertRedirectToRoute('feature-flags.index');

    $this->assertDatabaseHas('feature_flags', [
        'name' => 'Test Feature Flag',
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

it('fails validation with missing required fields on create', function () {
    [,$user] = createOwner();

    $this
        ->actingAs($user)
        ->post(route('feature-flags.store'), [])
        ->assertInvalid(['name', 'featureType']);
});

it('shows the edit pages', function (string $route) {
    [$team, $user] = createOwner();
    $featureType = FeatureType::factory()->for($team)->create();
    $tags = Tag::factory(3)->for($team)->create();
    $featureFlag = FeatureFlag::factory()->for($team)->for($featureType)->hasAttached($tags)->create();

    $this->actingAs($user)->get(route($route, ['feature_flag' => $featureFlag->id]))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('FeatureFlags/Edit')
                ->has('featureFlag')
                ->has('featureTypes', 5)
                ->has('tags', 3)
        );
})->with(['feature-flags.edit', 'feature-flags.edit.policy', 'feature-flags.edit.overview']);

it('updates a feature flag', function () {
    [$team, $user] = createOwner();

    $featureTypes = FeatureType::factory(3)->for($team)->create();
    $tags = Tag::factory(2)->for($team)->create();
    $featureFlag = FeatureFlag::factory()->for($team)->for($featureTypes->first())->hasAttached($tags->first())->create();

    $this
        ->actingAs($user)
        ->from(route('feature-flags.index'))
        ->put(route('feature-flags.update', ['feature_flag' => $featureFlag->id]), [
            'name' => 'ignored name',
            'description' => 'updated description',
            'feature_type' => \App\Values\FeatureType::from($featureTypes->last())->toArray(),
            'tags' => TagCollection::make($tags->toArray())->toArray(),
        ])
        ->assertRedirectToRoute('feature-flags.index');

    $this->assertDatabaseHas('feature_flags', [
        'name' => $featureFlag->name,
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

it('fails validation on update', function () {
    [$team, $user] = createOwner();
    $featureTypes = FeatureType::factory(3)->for($team)->create();
    $tags = Tag::factory(2)->for($team)->create();
    $featureFlag = FeatureFlag::factory()->for($team)->for($featureTypes->first())->hasAttached($tags->first())->create();

    $this
        ->actingAs($user)
        ->from(route('feature-flags.index'))
        ->put(route('feature-flags.update', ['feature_flag' => $featureFlag->name]), [])
        ->assertInvalid(['featureType']);
});
