<?php

declare(strict_types=1);

use App\Actions\Fortify\RejectInactive;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->rejectInactive = new RejectInactive();
});

it('throws ValidationException for users with inactive status', function () {
    // Create an inactive user
    User::factory()->create([
        'email' => 'inactive@example.com',
        'status' => UserStatus::INACTIVE,
    ]);

    // Set up the request with the inactive user email
    $request = Request::create('/login', 'POST', [
        'email' => 'inactive@example.com',
    ]);

    // Expect ValidationException when checking the inactive user
    expect(fn () => $this->rejectInactive->handle($request, fn ($req) => $req))
        ->toThrow(ValidationException::class);
});

it('allows active users to proceed', function () {
    // Create an active user (default factory state)
    User::factory()->create([
        'email' => 'active@example.com',
        'status' => UserStatus::ACTIVE,
    ]);

    // Set up the request with the active user email
    $request = Request::create('/login', 'POST', [
        'email' => 'active@example.com',
    ]);

    // The request should pass through the middleware
    $response = $this->rejectInactive->handle($request, fn ($req) => 'middleware_passed');

    // Verify middleware has passed
    expect($response)->toBe('middleware_passed');
});

it('allows non-existent users to proceed to next middleware', function () {
    // Set up the request with a non-existent user email
    $request = Request::create('/login', 'POST', [
        'email' => 'nonexistent@example.com',
    ]);

    // The request should pass through the middleware since validation
    // will be handled by another middleware
    $response = $this->rejectInactive->handle($request, fn ($req) => 'middleware_passed');

    // Verify middleware has passed
    expect($response)->toBe('middleware_passed');
});

it('allows users with pending status to proceed', function () {
    // Create a pending user
    User::factory()->create([
        'email' => 'pending@example.com',
        'status' => UserStatus::PENDING,
    ]);

    // Set up the request with the pending user email
    $request = Request::create('/login', 'POST', [
        'email' => 'pending@example.com',
    ]);

    // The request should pass through the middleware
    $response = $this->rejectInactive->handle($request, fn ($req) => 'middleware_passed');

    // Verify middleware has passed
    expect($response)->toBe('middleware_passed');
});
