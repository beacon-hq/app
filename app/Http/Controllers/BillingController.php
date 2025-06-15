<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ProductService;
use App\Services\SubscriptionBillingService;
use App\Values\Product;
use Illuminate\Support\Facades\App;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    public function index(ProductService $productService, SubscriptionBillingService $subscriptionBillingService): Response
    {
        $organization = App::context()->organization;

        return Inertia::render('Billing/Index', [
            'products' => $productService->all(),
            'subscription' => Product::from(
                $subscriptionBillingService->getSubscription($organization)
            )
        ]);
    }
}
