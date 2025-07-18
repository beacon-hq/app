<?php

declare(strict_types=1);

use App\Enums\Color;
use App\Models\Tag;
use Inertia\Testing\AssertableInertia as Assert;

it('lists tags', function () {
    [$team, $user] = createOwner();
    Tag::factory(5)->for($team)->create();

    $this->actingAs($user)->get(route('tags.index'))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Tags/Index')
                ->has('tags', 5)
        );
});

it('creates a tag', function () {
    [$team, $user] = createOwner();

    $this->actingAs($user)->post(route('tags.store'), [
        'name' => 'Test Tag',
        'color' => Color::CYAN->value,
        'description' => 'test description'
    ])->assertRedirectToRoute('tags.index');

    $this->assertDatabaseHas('tags', [
        'name' => 'Test Tag',
        'color' => Color::CYAN->value,
        'description' => 'test description',
        'team_id' => $team->id,
    ]);
});

it('fails validation with missing required fields on create', function () {
    [, $user] = createOwner();

    $this
        ->actingAs($user)
        ->post(route('tags.store'), [])
        ->assertInvalid(['name', 'color']);
});

it('shows the edit form', function () {
    [$team, $user] = createOwner();
    $tag = Tag::factory()->for($team)->create();

    $this->actingAs($user)->get(route('tags.edit', ['tag' => $tag->id]))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Tags/Edit')
                ->has('tag')
                ->where('tag', [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'description' => $tag->description,
                    'color' => $tag->color,
                    'created_at' => $tag->created_at->toISOString(),
                    'updated_at' => $tag->updated_at->toISOString(),
                ])
        );
});

it('updates a tag', function () {
    [$team, $user] = createOwner();
    $tag = Tag::factory()->for($team)->create();

    $this->actingAs($user)->put(route('tags.update', ['tag' => $tag->id]), [
        'name' => 'Updated Tag',
        'color' => Color::RED->value,
        'description' => 'updated description',
    ])->assertRedirectToRoute('tags.index');

    $this->assertDatabaseHas('tags', [
        'id' => $tag->id,
        'name' => 'Updated Tag',
        'color' => Color::RED->value,
        'description' => 'updated description',
    ]);
});

it('fails validation', function () {
    [$team, $user] = createOwner();
    $tag = Tag::factory()->for($team)->create();

    $this
        ->actingAs($user)
        ->put(route('tags.update', ['tag' => $tag->id]), [])
        ->assertInvalid(['name', 'color']);
});
