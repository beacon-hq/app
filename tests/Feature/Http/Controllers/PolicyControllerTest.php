<?php

declare(strict_types=1);

use App\Models\Policy;
use App\Models\Team;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('it lists policies', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    Policy::factory(5)->for($team)->create();

    $this->actingAs($user)->get('/policies')
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Policies/Index')
                ->has('policies', 5)
        );
});

it('it creates a policy', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();

    $this->actingAs($user)->post('/policies', [
        'name' => 'Test Policy',
        'description' => 'test description'
    ])->assertRedirect('/policies');

    $this->assertDatabaseHas('policies', [
        'name' => 'Test Policy',
        'slug' => 'test-policy',
        'description' => 'test description',
        'team_id' => $team->id,
    ]);
});

it('it fails validation with missing required fields on create', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();

    $this->actingAs($user)->post('/policies', [])->assertInvalid(['name', 'description']);
});

it('shows the edit form', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    $policy = Policy::factory()->for($team)->create();

    $this->actingAs($user)->get("/policies/{$policy->slug}/edit")
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Policies/Edit')
                ->has('policy')
                ->where('policy', [
                    'name' => $policy->name,
                    'description' => $policy->description,
                    'definition' => $policy->definition,
                    'slug' => $policy->slug,
                    'created_at' => $policy->created_at->toISOString(),
                    'updated_at' => $policy->updated_at->toISOString(),
                ])
        );
});

it('updates a policy', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    $policy = Policy::factory()->for($team)->create();

    $this->actingAs($user)->put("/policies/{$policy->slug}", [
        'name' => 'Updated Policy',
        'description' => 'updated description',
    ])->assertRedirect("/policies/{$policy->slug}/edit");

    $this->assertDatabaseHas('policies', [
        'name' => 'Updated Policy',
        'slug' => 'updated-policy',
        'description' => 'updated description',
    ]);
});

it('it fails validation with passing in name on update', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    $policy = Policy::factory()->for($team)->create();

    $this->actingAs($user)->put("/policies/{$policy->slug}", [])
        ->assertInvalid(['name', 'description']);
});
