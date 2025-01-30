<?php

declare(strict_types=1);

use App\Enums\Color;
use App\Models\Tag;
use App\Models\Team;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('it lists tags', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    Tag::factory(5)->for($team)->create();

    $this->actingAs($user)->get('/tags')
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Tags/Index')
                ->has('tags', 5)
        );
});

it('it creates a tag', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();

    $this->actingAs($user)->post('/tags', [
        'name' => 'Test Tag',
        'color' => Color::CYAN->value,
        'description' => 'test description'
    ])->assertRedirect('/tags');

    $this->assertDatabaseHas('tags', [
        'name' => 'Test Tag',
        'slug' => 'test-tag',
        'color' => Color::CYAN->value,
        'description' => 'test description',
        'team_id' => $team->id,
    ]);
});

it('it fails validation with missing required fields on create', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();

    $this->actingAs($user)->post('/tags', [])->assertInvalid(['name', 'color']);
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
