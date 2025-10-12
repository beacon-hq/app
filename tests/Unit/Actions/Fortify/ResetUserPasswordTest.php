<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Fortify;

use App\Actions\Fortify\ResetUserPassword;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $this->resetPasswordAction = new ResetUserPassword();
});

it('successfully resets a user password', function () {
    $this->resetPasswordAction->reset($this->user, [
        'password' => 'new-password123',
        'password_confirmation' => 'new-password123',
    ]);

    $this->user->refresh();

    // Verify the password was updated
    expect(Hash::check('new-password123', $this->user->password))->toBeTrue()
        ->and(Hash::check('old-password', $this->user->password))->toBeFalse();
});

it('validates password requirements', function () {
    $inputs = [
        // Too short
        [
            'password' => 'short',
            'password_confirmation' => 'short',
        ],
        // No confirmation
        [
            'password' => 'valid-password123',
        ],
        // Confirmation doesn't match
        [
            'password' => 'valid-password123',
            'password_confirmation' => 'different-password123',
        ],
        // Empty password
        [
            'password' => '',
            'password_confirmation' => '',
        ],
    ];

    foreach ($inputs as $input) {
        expect(fn () => $this->resetPasswordAction->reset($this->user, $input))
            ->toThrow(ValidationException::class);
    }
});

it('requires password confirmation to match', function () {
    expect(fn () => $this->resetPasswordAction->reset($this->user, [
        'password' => 'new-valid-password123',
        'password_confirmation' => 'different-password123',
    ]))->toThrow(ValidationException::class);
});

it('enforces minimum password length', function () {
    expect(fn () => $this->resetPasswordAction->reset($this->user, [
        'password' => 'short',
        'password_confirmation' => 'short',
    ]))->toThrow(ValidationException::class);
});
