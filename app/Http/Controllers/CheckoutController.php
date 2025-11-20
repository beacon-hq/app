<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\AppContext\GetAppContext;
use App\Actions\Products\ListProducts;
use App\Actions\Subscriptions\ChangeSubscription;
use App\Actions\Subscriptions\CreateSubscription;
use App\Actions\Subscriptions\HasActiveSubscription;
use App\Features\BillingEnabled;
use App\Values\Product;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Laravel\Cashier\Checkout;
use Laravel\Pennant\Feature;

class CheckoutController
{
    public function index()
    {
        if (!Feature::driver('array')->active(BillingEnabled::class)) {
            return \redirect('dashboard');
        }

        $organization = GetAppContext::organization();
        $hasActiveSubscription = false;

        if ($organization) {
            $hasActiveSubscription = HasActiveSubscription::run($organization);
        }

        return Inertia::render('Checkout/Index', [
            'products' => ListProducts::run(),
            'auth' => [
                'organization' => [
                    'has_active_subscription' => $hasActiveSubscription,
                ],
            ],
        ]);
    }

    public function show(Product $product): Checkout
    {
        return CreateSubscription::run(GetAppContext::organization(), $product);
    }

    public function update(Product $product): RedirectResponse
    {
        if (ChangeSubscription::run(GetAppContext::organization(), $product)) {
            return \redirect()->back()->withAlert('success', 'Subscription updated.');
        }

        return \redirect()->back()->withAlert('error', 'Failed to update subscription.');
    }
}
