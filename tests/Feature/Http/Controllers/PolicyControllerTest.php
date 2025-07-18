<?php

declare(strict_types=1);

use App\Models\Policy;
use Inertia\Testing\AssertableInertia as Assert;

it('lists policies', function () {
    [$team, $user] = createOwner();
    Policy::factory(5)->for($team)->create();

    $this->actingAs($user)->get(route('policies.index'))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Policies/Index')
                ->has('policies', 5)
        );
});

it('creates a policy', function () {
    [$team, $user] = createOwner();

    $this->actingAs($user)->post(route('policies.store'), [
        'name' => 'Test Policy',
        'description' => 'test description'
    ])->assertRedirectToRoute('policies.index');

    $this->assertDatabaseHas('policies', [
        'name' => 'Test Policy',
        'description' => 'test description',
        'team_id' => $team->id,
    ]);
});

it('fails validation with missing required fields on create', function () {
    [, $user] = createOwner();

    $this
        ->actingAs($user)
        ->post(route('policies.store'), [])
        ->assertInvalid(['name']);
});

it('shows the edit form', function () {
    [$team, $user] = createOwner();
    $policy = Policy::factory()->for($team)->create();

    $this->actingAs($user)->get(route('policies.edit', ['policy' => $policy->id]))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Policies/Edit')
                ->has('policy')
                ->where('policy', [
                    'name' => $policy->name,
                    'description' => $policy->description,
                    'definition' => json_decode($policy->definition->toJson(), true),
                    'id' => $policy->id,
                    'created_at' => $policy->created_at->toISOString(),
                    'updated_at' => $policy->updated_at->toISOString(),
                ])
        );
});

it('updates a policy', function () {
    [$team, $user] = createOwner();
    $policy = Policy::factory()->for($team)->create();

    $this->actingAs($user)->put(route('policies.update', ['policy' => $policy->id]), [
        'name' => 'Updated Policy',
        'description' => 'updated description',
    ])->assertRedirectToRoute('policies.edit', ['policy' => $policy->id]);

    $this->assertDatabaseHas('policies', [
        'name' => 'Updated Policy',
        'description' => 'updated description',
    ]);
});

it('fails validation on update', function () {
    [$team, $user] = createOwner();
    $policy = Policy::factory()->for($team)->create();

    $this->actingAs($user)->put(route('policies.update', ['policy' => $policy->id]), [])
        ->assertInvalid(['name']);
});
