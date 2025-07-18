<?php

declare(strict_types=1);

use App\Enums\Color;
use App\Models\Application;
use App\Models\Team;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('lists applications', function () {
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    Application::factory(5)->for($team)->create();

    $this->actingAs($user)->get(route('applications.index'))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Applications/Index')
                ->has('applications', 5)
        );
});

it('creates an application', function () {
    [$team, $user] = createOwner();

    $this->actingAs($user)->post(route('applications.store'), [
        'name' => 'Test Application',
        'display_name' => 'Test Application',
        'color' => Color::EMERALD->value,
    ])->assertRedirectToRoute('applications.index');

    $this->assertDatabaseHas('applications', [
        'name' => 'Test Application',
        'display_name' => 'Test Application',
        'color' => Color::EMERALD->value,
        'team_id' => $team->id,
    ]);
});

it('fails validation with missing required fields on create', function () {
    [$team, $user] = createOwner();

    $this
        ->actingAs($user)
        ->post(route('applications.store'), [])
        ->assertInvalid(['name']);
});

it('shows the edit form', function () {
    [$team, $user] = createOwner();
    $application = Application::factory()->for($team)->create();

    $this
        ->actingAs($user)
        ->get(route('applications.edit', ['application' => $application->id]))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Applications/Edit')
                ->has('application')
                ->where('application', [
                    'name' => $application->name,
                    'display_name' => $application->display_name,
                    'description' => $application->description,
                    'color' => $application->color,
                    'environments' => $application->environments->toArray(),
                    'id' => $application->id,
                    'last_seen_at' => $application->last_seen_at->toIso8601ZuluString('µ'),
                ])
        );
});

it('updates an application', function () {
    [$team, $user] = createOwner();
    $application = Application::factory()->for($team)->create();

    $this
        ->actingAs($user)
        ->put(route('applications.update', ['application' => $application->id]), [
            'display_name' => 'Updated Application',
            'color' => Color::EMERALD->value,
            'description' => 'updated description',
        ])
        ->assertRedirectToRoute('applications.index');

    $this->assertDatabaseHas('applications', [
        'name' => $application->name,
        'display_name' => 'Updated Application',
        'color' => Color::EMERALD->value,
        'description' => 'updated description',
    ]);
});

it('fails validation with missing required fields on update', function () {
    [$team, $user] = createOwner();
    $application = Application::factory()->for($team)->create();

    $this
        ->actingAs($user)
        ->put(route('applications.update', ['application' => $application->id]), [
            'displayName' => '',
        ])
        ->assertInvalid(['displayName']);
});
