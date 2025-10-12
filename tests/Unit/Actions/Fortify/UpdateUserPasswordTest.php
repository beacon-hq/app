<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Fortify;

use App\Actions\Fortify\UpdateUserPassword;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Session;

beforeEach(function () {
    $this->user = User::factory()->create([
        'password' => Hash::make('current-password'),
    ]);

    Session::spy();

    $this->updateUserPasswordAction = app(UpdateUserPassword::class);
});

it('can update a user password', function () {
    $this->updateUserPasswordAction->update($this->user, [
        'current_password' => 'current-password',
        'password' => 'new-password123',
        'password_confirmation' => 'new-password123',
    ]);

    $this->user->refresh();

    expect(Hash::check('new-password123', $this->user->password))->toBeTrue()
        ->and(Hash::check('current-password', $this->user->password))->toBeFalse();

    \Illuminate\Support\Facades\Session::shouldHaveReceived('flash')
        ->with('alert', [
            'message' => 'Password updated successfully.',
            'status' => 'success',
        ]);
});
