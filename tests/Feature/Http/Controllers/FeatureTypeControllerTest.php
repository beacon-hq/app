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

    $this->actingAs($user)->get(route('feature-types.index'))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('FeatureTypes/Index')
                ->has('featureTypes', 9)
        );
});

it('creates a feature type', function () {
    [$team, $user] = createOwner();

    $this->actingAs($user)->post(route('feature-types.store'), [
        'name' => 'Test Feature Type',
        'color' => Color::EMERALD->value,
        'icon' => 'Rocket',
    ])->assertRedirectToRoute('feature-types.index');

    $this->assertDatabaseHas('feature_types', [
        'name' => 'Test Feature Type',
        'color' => Color::EMERALD->value,
        'icon' => 'Rocket',
        'team_id' => $team->id,
    ]);
});

it('fails validation with missing required fields on create', function () {
    [$team, $user] = createOwner();

    $this
        ->actingAs($user)
        ->post(route('feature-types.store'), [])
        ->assertInvalid(['name', 'icon']);
});

it('shows the edit form', function () {
    [$team, $user] = createOwner();
    $featureType = FeatureType::factory()->for($team)->create();

    $this->actingAs($user)->get(route('feature-types.edit', ['feature_type' => $featureType->id]))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('FeatureTypes/Edit')
                ->has('featureType')
                ->where('featureType', [
                    'id' => $featureType->id,
                    'color' => $featureType->color,
                    'icon' => $featureType->icon,
                    'description' => $featureType->description,
                    'name' => $featureType->name,
                    'temporary' => $featureType->temporary,
                    'created_at' => $featureType->created_at->toISOString(),
                    'updated_at' => $featureType->updated_at->toISOString(),
                    'is_default' => $featureType->is_default,
                ])
        );
});

it('updates an feature flag', function () {
    [$team, $user] = createOwner();
    $featureType = FeatureType::factory()->for($team)->create();

    $this->actingAs($user)->put(route('feature-types.update', ['feature_type' => $featureType->id]), [
        'icon' => 'Wrench',
        'color' => Color::RED->value,
        'description' => 'updated description',
    ])->assertRedirectToRoute('feature-types.index');

    $this->assertDatabaseHas('feature_types', [
        'name' => $featureType->name,
        'color' => Color::RED->value,
        'icon' => 'Wrench',
        'description' => 'updated description',
    ]);
});

it('fails validation with passing in name on update', function () {
    [$team, $user] = createOwner();
    $featureType = FeatureType::factory()->for($team)->create();

    $this->actingAs($user)->put(route('feature-types.update', ['feature_type' => $featureType->id]), [])
        ->assertInvalid(['color', 'icon']);
});

it('can set a feature type as default', function () {
    [$team, $user] = createOwner();
    $featureType = FeatureType::factory()->for($team)->create(['is_default' => false]);

    $this->actingAs($user)->patch(route('feature-types.set-default', ['feature_type' => $featureType->id]))
        ->assertRedirectToRoute('feature-types.index');

    $this->assertDatabaseHas('feature_types', [
        'id' => $featureType->id,
        'is_default' => true,
    ]);
});

it('ensures only one feature type can be default', function () {
    [$team, $user] = createOwner();
    $featureType1 = FeatureType::factory()->for($team)->create(['is_default' => true]);
    $featureType2 = FeatureType::factory()->for($team)->create(['is_default' => false]);

    $this->actingAs($user)->patch(route('feature-types.set-default', ['feature_type' => $featureType2->id]))
        ->assertRedirectToRoute('feature-types.index');

    $this->assertDatabaseHas('feature_types', [
        'id' => $featureType1->id,
        'is_default' => false,
    ]);

    $this->assertDatabaseHas('feature_types', [
        'id' => $featureType2->id,
        'is_default' => true,
    ]);
});

it('can create a feature type with default flag', function () {
    [$team, $user] = createOwner();

    $this->actingAs($user)->post(route('feature-types.store'), [
        'name' => 'Default Feature Type',
        'color' => Color::EMERALD->value,
        'icon' => 'Rocket',
        'is_default' => true,
    ])->assertRedirectToRoute('feature-types.index');

    $this->assertDatabaseHas('feature_types', [
        'name' => 'Default Feature Type',
        'is_default' => true,
    ]);
});

it('clears existing default when creating new default feature type', function () {
    [$team, $user] = createOwner();
    $existingDefault = FeatureType::factory()->for($team)->create(['is_default' => true]);

    $this->actingAs($user)->post(route('feature-types.store'), [
        'name' => 'New Default Feature Type',
        'color' => Color::EMERALD->value,
        'icon' => 'Rocket',
        'is_default' => true,
    ])->assertRedirectToRoute('feature-types.index');

    $this->assertDatabaseHas('feature_types', [
        'id' => $existingDefault->id,
        'is_default' => false,
    ]);

    $this->assertDatabaseHas('feature_types', [
        'name' => 'New Default Feature Type',
        'is_default' => true,
    ]);
});
