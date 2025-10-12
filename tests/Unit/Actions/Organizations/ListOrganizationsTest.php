<?php

declare(strict_types=1);

use App\Actions\Organizations\ListOrganizations;
use App\Models\Organization;
use App\Models\User;
use App\Values\Collections\OrganizationCollection;
use App\Values\User as UserValue;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->userValue = UserValue::from($this->user);

    // Create organizations for the user
    $this->organization1 = Organization::factory()->create([
        'owner_id' => $this->user->id,
        'name' => 'Test Organization 1',
    ]);

    $this->organization2 = Organization::factory()->create([
        'owner_id' => $this->user->id,
        'name' => 'Test Organization 2',
    ]);

    // Attach user to organizations
    $this->organization1->users()->attach($this->user->id);
    $this->organization2->users()->attach($this->user->id);

    // Create an organization not owned by the user
    $otherUser = User::factory()->create();
    $this->otherOrganization = Organization::factory()->create([
        'owner_id' => $otherUser->id,
        'name' => 'Other Organization',
    ]);

    $this->listOrganizationsAction = ListOrganizations::make();
});

it('returns all organizations for a user', function () {
    $result = $this->listOrganizationsAction->handle($this->userValue);

    expect($result)->toHaveCount(2)
        ->and($result->first()->name)->toBe('Test Organization 1')
        ->and($result->last()->name)->toBe('Test Organization 2');
});

it('returns organizations as an OrganizationCollection', function () {
    $result = $this->listOrganizationsAction->handle($this->userValue);

    expect($result)->toBeInstanceOf(OrganizationCollection::class);
});

it('returns an empty collection when user has no organizations', function () {
    // Create a new user with no organizations
    $newUser = User::factory()->create();
    $newUserValue = UserValue::from($newUser);

    $result = $this->listOrganizationsAction->handle($newUserValue);

    expect($result)->toHaveCount(0)
        ->and($result)->toBeEmpty();
});
