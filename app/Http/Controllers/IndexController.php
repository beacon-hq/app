<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\MailService;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndexController extends Controller
{
    public function index(ProductService $productService)
    {
        return Inertia::render('Home/Index', [
            'products' => $productService->all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, MailService $mailService)
    {
        $request->validate([
            'email' => [
                'required',
                'email'
            ]
        ]);

        $mailService->addContact($request->string('email'));

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
