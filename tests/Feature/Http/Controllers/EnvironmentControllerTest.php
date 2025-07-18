<?php

declare(strict_types=1);

use App\Enums\Color;
use App\Models\Environment;
use Inertia\Testing\AssertableInertia as Assert;

it('lists environments', function () {
    [$team, $user] = createOwner();
    Environment::factory(5)->for($team)->create();

    $this
        ->actingAs($user)
        ->get(route('environments.index'))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Environments/Index')
                ->has('environments', 5)
        );
});

it('creates an environment', function () {
    [$team, $user] = createOwner();

    $this->actingAs($user)->post(route('environments.store'), [
        'name' => 'Test Environment',
        'color' => Color::EMERALD->value,
    ])->assertRedirectToRoute('environments.index');

    $this->assertDatabaseHas('environments', [
        'name' => 'Test Environment',
        'color' => Color::EMERALD->value,
        'team_id' => $team->id,
    ]);
});

it('fails validation with missing required fields on create', function () {
    [$team, $user] = createOwner();

    $this
        ->actingAs($user)
        ->post(route('environments.store'), [])
        ->assertInvalid(['name']);
});

it('shows the edit form', function () {
    [$team, $user] = createOwner();
    $environment = Environment::factory()->for($team)->create();

    $this->actingAs($user)->get(route('environments.edit', ['environment' => $environment->id]))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Environments/Edit')
                ->has('environment')
                ->where('environment', [
                    'color' => $environment->color,
                    'description' => $environment->description,
                    'id' => $environment->id,
                    'name' => $environment->name,
                    'last_seen_at' => null,
                ])
        );
});

it('updates an environment', function () {
    [$team, $user] = createOwner();
    $environment = Environment::factory()->for($team)->create();

    $this->actingAs($user)->put(route('environments.update', ['environment' => $environment->id]), [
        'color' => Color::RED->value,
        'description' => 'updated description',
    ])->assertRedirectToRoute('environments.index');

    $this->assertDatabaseHas('environments', [
        'name' => $environment->name,
        'color' => Color::RED->value,
        'description' => 'updated description',
    ]);
});

it('fails validation with passing in name on update', function () {
    [$team, $user] = createOwner();
    $environment = Environment::factory()->for($team)->create();

    $this->actingAs($user)->put(route('environments.update', ['environment' => $environment->id]), [])
        ->assertInvalid(['color']);
});
