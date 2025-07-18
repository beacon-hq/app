<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Session;

class SetPasswordConfirmationListener
{
    public function handle(Login $login): void
    {
        Session::put('auth.password_confirmed_at', time());
    }
}
