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
        return Inertia::render('Home/Index');
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
