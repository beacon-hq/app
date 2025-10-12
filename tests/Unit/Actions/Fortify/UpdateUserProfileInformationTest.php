<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Fortify;

use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Notification;

beforeEach(function () {
    Storage::fake('public');

    $this->user = User::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'theme' => 'light'
    ]);

    $this->request = Request::create('/', 'POST');

    Session::spy();

    $this->updateProfileAction = new UpdateUserProfileInformation($this->request);
});

test('it validates required input fields', function () {
    $this->updateProfileAction->update($this->user, []);
})->throws(ValidationException::class);

test('it validates email format', function () {
    $this->updateProfileAction->update($this->user, [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'invalid-email',
    ]);
})->throws(ValidationException::class);

test('it validates unique email', function () {
    $anotherUser = User::factory()->create(['email' => 'another@example.com']);

    $this->updateProfileAction->update($this->user, [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'another@example.com',
    ]);
})->throws(ValidationException::class);

test('it validates theme options', function () {
    $this->updateProfileAction->update($this->user, [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'theme' => 'invalid-theme',
    ]);
})->throws(ValidationException::class);

test('it successfully updates profile without avatar', function () {
    $this->updateProfileAction->update($this->user, [
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'email' => 'jane@example.com',
        'theme' => 'dark',
    ]);

    $this->user->refresh();

    expect($this->user->first_name)->toBe('Jane')
        ->and($this->user->last_name)->toBe('Smith')
        ->and($this->user->email)->toBe('jane@example.com')
        ->and($this->user->theme)->toBe('dark')
        ->and($this->user->avatar_url)->toBeNull();

    Session::shouldHaveReceived('flash')
        ->with('alert', [
            'message' => 'Profile updated successfully.',
            'status' => 'success',
        ]);
});

test('it successfully updates profile with avatar', function () {
    $file = UploadedFile::fake()->image('avatar.jpg', 500, 500);

    $this->request->files->set('avatar', $file);

    $this->updateProfileAction->update($this->user, [
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'email' => 'jane@example.com',
        'avatar' => $file,
        'theme' => 'dark',
    ]);

    $this->user->refresh();

    Storage::disk('public')->assertExists($this->user->avatar_url);

    expect($this->user->first_name)->toBe('Jane')
        ->and($this->user->last_name)->toBe('Smith')
        ->and($this->user->email)->toBe('jane@example.com')
        ->and($this->user->theme)->toBe('dark')
        ->and($this->user->avatar_url)->not->toBeNull();

    Session::shouldHaveReceived('flash')
        ->with('alert', [
            'message' => 'Profile updated successfully.',
            'status' => 'success',
        ]);
});

test('it defaults to current theme when theme not provided', function () {
    $this->updateProfileAction->update($this->user, [
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'email' => 'jane@example.com',
    ]);

    $this->user->refresh();

    expect($this->user->theme)->toBe('light');
});

test('it sends verification email when email changes for verified users', function () {
    Notification::fake();

    $verifiableUser = User::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'verified@example.com',
        'email_verified_at' => now(),
        'theme' => 'light',
    ]);

    $request = Request::create('/', 'POST');
    $action = new UpdateUserProfileInformation($request);

    $action->update($verifiableUser, [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'new-verified@example.com',
    ]);

    $verifiableUser->refresh();
    expect($verifiableUser->first_name)->toBe('John')
        ->and($verifiableUser->last_name)->toBe('Doe')
        ->and($verifiableUser->email)->toBe('new-verified@example.com')
        ->and($verifiableUser->email_verified_at)->toBeNull();

    Notification::assertSentTo($verifiableUser, VerifyEmail::class);
});

test('it does not send verification email when email does not change', function () {
    Notification::fake();

    $verifiableUser = User::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'same@example.com',
        'email_verified_at' => now(),
        'theme' => 'light',
    ]);

    $request = Request::create('/', 'POST');
    $action = new UpdateUserProfileInformation($request);

    $action->update($verifiableUser, [
        'first_name' => 'John Updated',
        'last_name' => 'Doe Updated',
        'email' => 'same@example.com',
        'theme' => 'light',
    ]);

    $verifiableUser->refresh();

    expect($verifiableUser->first_name)->toBe('John Updated')
        ->and($verifiableUser->last_name)->toBe('Doe Updated')
        ->and($verifiableUser->email)->toBe('same@example.com')
        ->and($verifiableUser->email_verified_at)->not->toBeNull();

    Notification::assertNothingSent();
});
