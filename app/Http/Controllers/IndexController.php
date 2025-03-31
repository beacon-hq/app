<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Resend;

class IndexController extends Controller
{
    public function index()
    {
        return Inertia::render('Welcome');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => [
                'required',
                'email'
            ]
        ]);

        $resend = Resend::client(\config('resend.api_key'));
        $resend->contacts->create(
            audienceId: config('resend.audience_id'),
            parameters: [
                'email' => $request->string('email'),
                'unsubscribed' => false
            ]
        );

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
