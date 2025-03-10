<?php

declare(strict_types=1);

use App\Enums\Color;
use App\Models\FeatureType;
use App\Models\Team;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('lists feature types', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    FeatureType::factory(5)->for($team)->create();

    $this->actingAs($user)->get('/feature-types')
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('FeatureTypes/Index')
                ->has('featureTypes', 5)
        );
});

it('creates a feature type', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();

    $this->actingAs($user)->post('/feature-types', [
        'name' => 'Test Feature Type',
        'color' => Color::EMERALD->value,
        'icon' => 'Rocket',
    ])->assertRedirect('/feature-types');

    $this->assertDatabaseHas('feature_types', [
        'name' => 'Test Feature Type',
        'slug' => 'test-feature-type',
        'color' => Color::EMERALD->value,
        'icon' => 'Rocket',
        'team_id' => $team->id,
    ]);
});

it('fails validation with missing required fields on create', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();

    $this->actingAs($user)->post('/feature-types', [])->assertInvalid(['name', 'color', 'icon']);
});

it('shows the edit form', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    $featureType = FeatureType::factory()->for($team)->create();

    $this->actingAs($user)->get("/feature-types/{$featureType->slug}/edit")
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('FeatureTypes/Edit')
                ->has('featureType')
                ->where('featureType', [
                    'color' => $featureType->color,
                    'icon' => $featureType->icon,
                    'description' => $featureType->description,
                    'name' => $featureType->name,
                    'slug' => $featureType->slug,
                    'temporary' => $featureType->temporary,
                    'created_at' => $featureType->created_at->toISOString(),
                    'updated_at' => $featureType->updated_at->toISOString(),
                ])
        );
});

it('updates an feature flag', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    $featureType = FeatureType::factory()->for($team)->create();

    $this->actingAs($user)->put("/feature-types/{$featureType->slug}", [
        'icon' => 'Wrench',
        'color' => Color::RED->value,
        'description' => 'updated description',
    ])->assertRedirect('/feature-types');

    $this->assertDatabaseHas('feature_types', [
        'name' => $featureType->name,
        'slug' => $featureType->slug,
        'color' => Color::RED->value,
        'icon' => 'Wrench',
        'description' => 'updated description',
    ]);
});

it('fails validation with passing in name on update', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    $featureType = FeatureType::factory()->for($team)->create();

    $this->actingAs($user)->put("/feature-types/{$featureType->slug}", [])
        ->assertInvalid(['color', 'icon']);
});
