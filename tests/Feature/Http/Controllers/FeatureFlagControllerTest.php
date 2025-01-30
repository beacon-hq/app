<?php

declare(strict_types=1);

use App\Enums\Color;
use App\Models\FeatureFlag;
use App\Models\FeatureType;
use App\Models\Tag;
use App\Models\Team;
use App\Models\User;
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

it('it creates a tag', function () {
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

it('shows the edit form', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    $tag = Tag::factory()->for($team)->create();

    $this->actingAs($user)->get("/tags/{$tag->slug}/edit")
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Tags/Edit')
                ->has('tag')
                ->where('tag', [
                    'name' => $tag->name,
                    'description' => $tag->description,
                    'color' => $tag->color,
                    'slug' => $tag->slug,
                    'created_at' => $tag->created_at->toISOString(),
                    'updated_at' => $tag->updated_at->toISOString(),
                ])
        );
});

it('updates a tag', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    $tag = Tag::factory()->for($team)->create();

    $this->actingAs($user)->put("/tags/{$tag->slug}", [
        'name' => 'Updated Tag',
        'color' => Color::RED->value,
        'description' => 'updated description',
    ])->assertRedirect('/tags');

    $this->assertDatabaseHas('tags', [
        'name' => 'Updated Tag',
        'color' => Color::RED->value,
        'slug' => 'updated-tag',
        'description' => 'updated description',
    ]);
});

it('it fails validation with passing in name on update', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    $tag = Tag::factory()->for($team)->create();

    $this->actingAs($user)->put("/tags/{$tag->slug}", [])
        ->assertInvalid(['name', 'color']);
});
