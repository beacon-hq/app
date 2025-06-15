<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ProductService;
use App\Services\SubscriptionBillingService;
use App\Values\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;
use Inertia\Inertia;
use Laravel\Cashier\Checkout;

class CheckoutController
{
    public function index(ProductService $productService, SubscriptionBillingService $subscriptionBillingService)
    {
        $organization = App::context()->organization;
        $hasActiveSubscription = false;

        if ($organization) {
            $hasActiveSubscription = $subscriptionBillingService->hasActiveSubscription($organization);
        }

        return Inertia::render('Checkout/Index', [
            'products' => $productService->all(),
            'auth' => [
                'organization' => [
                    'has_active_subscription' => $hasActiveSubscription,
                ],
            ],
        ]);
    }

    public function show(Product $product, SubscriptionBillingService $subscriptionBillingService): Checkout
    {
        return $subscriptionBillingService->createSubscription(App::context()->organization, $product);
    }

    public function update(Product $product, SubscriptionBillingService $subscriptionBillingService): RedirectResponse
    {
        $organization = App::context()->organization;

        if ($subscriptionBillingService->changeSubscription($organization, $product)) {
            return \redirect()->back()->withAlert('success', 'Subscription updated.');
        }

        return \redirect()->back()->withAlert('error', 'Failed to update subscription.');
    }
}
