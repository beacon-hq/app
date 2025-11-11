<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Mail\AddContact;
use App\Features\BeaconEnabled;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Laravel\Pennant\Feature;

class IndexController
{
    public function index()
    {
        if (Feature::driver('array')->active(BeaconEnabled::class)) {
            return Inertia::render('Home/Index');
        }

        return Inertia::render('Home/LandingPage', [
            'waitlistEnabled' => Feature::active('mailing-list-enabled'),
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
