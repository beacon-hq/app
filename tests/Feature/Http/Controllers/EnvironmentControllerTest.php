<?php

declare(strict_types=1);

use App\Enums\Color;
use App\Models\Environment;
use App\Models\Team;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('lists environments', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    Environment::factory(5)->for($team)->create();

    $this->actingAs($user)->get('/environments')
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Environments/Index')
                ->has('environments', 5)
        );
});

it('creates an environment', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();

    $this->actingAs($user)->post('/environments', [
        'name' => 'Test Environment',
        'color' => Color::EMERALD->value,
    ])->assertRedirect('/environments');

    $this->assertDatabaseHas('environments', [
        'name' => 'Test Environment',
        'slug' => 'test-environment',
        'color' => Color::EMERALD->value,
        'team_id' => $team->id,
    ]);
});

it('fails validation with missing required fields on create', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();

    $this->actingAs($user)->post('/environments', [])->assertInvalid(['name', 'color']);
});

it('shows the edit form', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    $environment = Environment::factory()->for($team)->create();

    $this->actingAs($user)->get("/environments/{$environment->slug}/edit")
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Environments/Edit')
                ->has('environment')
                ->where('environment', [
                    'color' => $environment->color,
                    'description' => $environment->description,
                    'id' => $environment->id,
                    'name' => $environment->name,
                    'slug' => $environment->slug,
                ])
        );
});

it('updates an environment', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    $environment = Environment::factory()->for($team)->create();

    $this->actingAs($user)->put("/environments/{$environment->slug}", [
        'color' => Color::RED->value,
        'description' => 'updated description',
    ])->assertRedirect('/environments');

    $this->assertDatabaseHas('environments', [
        'name' => $environment->name,
        'slug' => $environment->slug,
        'color' => Color::RED->value,
        'description' => 'updated description',
    ]);
});

it('fails validation with passing in name on update', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    $environment = Environment::factory()->for($team)->create();

    $this->actingAs($user)->put("/environments/{$environment->slug}", [])
        ->assertInvalid(['color']);
});
