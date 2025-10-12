<?php

declare(strict_types=1);

use App\Actions\Fortify\CreateNewUser;
use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use App\Values\Invite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->createNewUser = new CreateNewUser();
});

it('creates a new user with valid data', function () {
    $userData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];

    $user = $this->createNewUser->create($userData);

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->first_name)->toBe('John')
        ->and($user->last_name)->toBe('Doe')
        ->and($user->email)->toBe('john.doe@example.com')
        ->and($user->status)->toBe(UserStatus::ACTIVE);
});

it('validates required fields', function (string $field) {
    $userData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];

    // Remove the field we want to test
    unset($userData[$field]);

    expect(fn () => $this->createNewUser->create($userData))
        ->toThrow(ValidationException::class);
})->with([
    'first_name',
    'last_name',
    'email',
    'password',
]);

it('validates email format', function () {
    $userData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'not-an-email',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];

    expect(fn () => $this->createNewUser->create($userData))
        ->toThrow(ValidationException::class);
});

it('prevents duplicate emails', function () {
    // Create a user first
    User::factory()->create([
        'email' => 'duplicate@example.com',
    ]);

    // Try to create another user with the same email
    $userData = [
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'email' => 'duplicate@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];

    expect(fn () => $this->createNewUser->create($userData))
        ->toThrow(ValidationException::class);
});

it('stores the selected plan in the session', function () {
    // Create a product in the database
    $planId = 'plan_' . uniqid();
    DB::table('stripe_products')->insert([
        'id' => $planId,
        'stripe_id' => 'stripe_' . $planId,
        'name' => 'Test Plan',
        'description' => 'A test plan for unit tests',
        'active' => true,
        'entitlements' => json_encode(['feature' => 'basic']),
        'metadata' => json_encode(['test' => true]),
        'order' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $userData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'plan-user@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'plan' => $planId,
    ];

    // Create user with plan
    $this->createNewUser->create($userData);

    // Check that the plan was stored in the session
    expect(Session::get('billing.plan'))->toBe($planId);
});

it('handles invitations when creating a user', function () {
    // Create organization, team, and user for the invitation
    $organization = Organization::factory()->create();
    $team = Team::factory()->for($organization)->create();
    $inviter = User::factory()->create();

    // Create an actual invite in the database
    $invite = \App\Models\Invite::create([
        'email' => 'invited@example.com',
        'role' => Role::DEVELOPER,
        'team_id' => $team->id,
        'organization_id' => $organization->id,
        'user_id' => $inviter->id,
        'expires_at' => now()->addDays(7),
    ]);

    $inviteValue = Invite::from($invite);

    Session::put('invite', $inviteValue);

    $userData = [
        'first_name' => 'Invited',
        'last_name' => 'User',
        'email' => 'invited@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];

    $user = $this->createNewUser->create($userData);

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->first_name)->toBe('Invited')
        ->and($user->last_name)->toBe('User')
        ->and($user->email)->toBe('invited@example.com');

    expect(Session::has('invite'))->toBeFalse();
});

it('throws an exception when validation fails', function () {
    // Invalid email format should fail validation
    $userData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'not-a-valid-email',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];

    expect(fn () => $this->createNewUser->create($userData))
        ->toThrow(ValidationException::class);
});
