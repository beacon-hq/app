<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Products\FindProduct;
use App\Values\Invite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController
{
    /**
     * Display the registration view.
     */
    public function create(Request $request, FindProduct $findProductAction): Response
    {
        /** @var Invite $invite */
        $invite = Session::get('invite');

        return Inertia::render('Auth/Register', [
            'invite' => !$invite || $invite->expires_at->isAfter(now()) ? $invite : false,
            'plan' => $request->has('plan') ? $findProductAction->handle($request->get('plan')) : null,
        ]);
    }
}
