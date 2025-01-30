<?php

declare(strict_types=1);

use App\Enums\Color;
use App\Models\Application;
use App\Models\Team;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('it lists applications', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    Application::factory(5)->for($team)->create();

    $this->actingAs($user)->get('/applications')
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Applications/Index')
                ->has('applications', 5)
        );
});

it('it creates an application', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();

    $this->actingAs($user)->post('/applications', [
        'name' => 'Test Application',
        'display_name' => 'Test Application',
        'color' => Color::EMERALD->value,
    ])->assertRedirect('/applications');

    $this->assertDatabaseHas('applications', [
        'name' => 'Test Application',
        'display_name' => 'Test Application',
        'slug' => 'test-application',
        'color' => Color::EMERALD->value,
        'team_id' => $team->id,
    ]);
});

it('it fails validation with missing required fields on create', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();

    $this->actingAs($user)->post('/applications', [
    ])->assertInvalid(['name', 'color']);
});

it('shows the edit form', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    $application = Application::factory()->for($team)->create();

    $this->actingAs($user)->get("/applications/{$application->slug}/edit")
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Applications/Edit')
                ->has('application')
                ->where('application', [
                    'name' => $application->name,
                    'display_name' => $application->display_name,
                    'slug' => $application->slug,
                    'description' => $application->description,
                    'color' => $application->color,
                    'environments' => $application->environments->toArray(),
                    'id' => $application->id,
                    'last_seen_at' => (string) $application->last_seen_at,
                ])
        );
});

it('updates an application', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    $application = Application::factory()->for($team)->create();

    $this->actingAs($user)->put("/applications/{$application->slug}", [
        'display_name' => 'Updated Application',
        'color' => Color::EMERALD->value,
        'description' => 'updated description',
    ])->assertRedirect('/applications');

    $this->assertDatabaseHas('applications', [
        'name' => $application->name,
        'display_name' => 'Updated Application',
        'slug' => $application->slug,
        'color' => Color::EMERALD->value,
        'description' => 'updated description',
    ]);
});

it('it fails validation with missing required fields on update', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    $application = Application::factory()->for($team)->create();

    $this
        ->actingAs($user)
        ->put("/applications/{$application->slug}", [])
        ->assertInvalid(['color']);
});
