<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Session;
use Storage;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    public function __construct(protected Request $request)
    {
    }

    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, string>  $input
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'avatar' => ['nullable', 'file', 'image', 'max:1024'],
        ])->validateWithBag('updateProfileInformation');

        $input['avatar_url'] = null;
        if ($this->request->hasFile('avatar')) {
            $avatar = Storage::disk('public')->putFile('avatars', $this->request->file('avatar'));
            $input['avatar_url'] = $avatar;
            unset($input['avatar']);
        }

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'email' => $input['email'],
                'avatar_url' => $input['avatar_url']
            ])->save();
        }

        Session::flash(
            'alert',
            [
                'message' => 'Profile updated successfully.',
                'status' => 'success',
            ]
        );
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'email' => $input['email'],
            'avatar_url' => $input['avatar_url'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
