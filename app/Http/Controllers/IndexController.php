<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Mail\AddContact;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Laravel\Pennant\Feature;

class IndexController
{
    public function index()
    {
        if (Feature::active('beacon-enabled')) {
            return Inertia::render('Home/Index');
        }

        return Inertia::render('Home/LandingPage', [
            'waitlistEnabled' => Feature::active('waitlist-enabled'),
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
