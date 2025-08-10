<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

class RejectInactive
{
    public function handle(Request $request, callable $next)
    {
        if (User::where('email', $request->email)->where('status', UserStatus::INACTIVE())->exists()) {
            throw ValidationException::withMessages([
                Fortify::username() => [trans('auth.failed')],
            ]);
        }

        return $next($request);
    }
}
