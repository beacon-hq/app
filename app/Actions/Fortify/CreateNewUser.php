<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App;
use App\Enums\UserStatus;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Session;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function __construct(protected UserService $userService)
    {
    }

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
            'plan' => ['nullable', 'exists:stripe_products,id'],
        ])->validate();

        Session::put('auth.password_confirmed_at', time());

        if (isset($input['plan'])) {
            Session::put('billing.plan', $input['plan']);
        }

        try {
            return DB::transaction(function () use ($input) {
                return User::find($this->userService->create(
                    App\Values\User::from(
                        firstName: $input['first_name'],
                        lastName: $input['last_name'],
                        email: $input['email'],
                        password: $input['password'],
                        status: UserStatus::ACTIVE,
                    ),
                    Session::has('invite') ? Session::get('invite') : null,
                )->id);
            });
        } catch (\Exception $e) {
            Auth::hasUser() && Auth::logout();

            throw $e;
        }
    }
}
