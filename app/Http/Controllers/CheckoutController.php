<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App;
use App\Services\ProductService;
use App\Values\Product;
use Inertia\Inertia;
use Laravel\Cashier\Checkout;

class CheckoutController
{
    public function index(ProductService $productService)
    {
        return Inertia::render('Checkout/Index', [
            'products' => $productService->all(),
        ]);
    }

    public function show(Product $product, App\Services\OrganizationService $organizationService): Checkout
    {
        return $organizationService->createSubscription(App::context()->organization, $product);
    }
}
