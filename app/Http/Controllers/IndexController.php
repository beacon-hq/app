<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Mail\AddContact;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndexController
{
    public function index()
    {
        if (config('beacon.enabled')) {
            return Inertia::render('Home/Index');
        }

        return Inertia::render('Home/LandingPage', [
            'waitlistEnabled' => config('beacon.waitlist.enabled'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => [
                'required',
                'email'
            ]
        ]);

        AddContact::run($request->string('email'));

        return back();
    }
}
