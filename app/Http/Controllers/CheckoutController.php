<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\AppContext\GetAppContext;
use App\Actions\Products\ListProducts;
use App\Actions\Subscriptions\ChangeSubscription;
use App\Actions\Subscriptions\CreateSubscription;
use App\Actions\Subscriptions\HasActiveSubscription;
use App\Values\Product;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Laravel\Cashier\Checkout;

class CheckoutController
{
    public function index(ListProducts $listProductsAction, HasActiveSubscription $hasActiveSubscriptionAction)
    {
        if (!config('beacon.billing.enabled')) {
            return \redirect('dashboard');
        }

        $organization = GetAppContext::organization();
        $hasActiveSubscription = false;

        if ($organization) {
            $hasActiveSubscription = $hasActiveSubscriptionAction->handle($organization);
        }

        return Inertia::render('Checkout/Index', [
            'products' => $listProductsAction->handle(),
            'auth' => [
                'organization' => [
                    'has_active_subscription' => $hasActiveSubscription,
                ],
            ],
        ]);
    }

    public function show(Product $product, CreateSubscription $createSubscriptionAction): Checkout
    {
        return $createSubscriptionAction->handle(GetAppContext::organization(), $product);
    }

    public function update(Product $product, ChangeSubscription $changeSubscriptionAction): RedirectResponse
    {
        if ($changeSubscriptionAction->handle(GetAppContext::organization(), $product)) {
            return \redirect()->back()->withAlert('success', 'Subscription updated.');
        }

        return \redirect()->back()->withAlert('error', 'Failed to update subscription.');
    }
}
