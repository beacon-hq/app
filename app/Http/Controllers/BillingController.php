<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\AppContext\GetAppContext;
use App\Actions\Metrics\GetPlanMetrics;
use App\Actions\Products\ListProducts;
use App\Actions\Subscriptions\CancelSubscription;
use App\Actions\Subscriptions\DownloadInvoice;
use App\Actions\Subscriptions\GetInvoices;
use App\Actions\Subscriptions\GetPeriodEndDate;
use App\Actions\Subscriptions\GetSubscription;
use App\Actions\Subscriptions\GetSubscriptionStatus;
use App\Actions\Subscriptions\GetTrialEndDate;
use App\Actions\Subscriptions\PredictNextBill;
use App\Actions\Subscriptions\ResumeSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class BillingController
{
    public function index(
    ): RedirectResponse|Response {
        if (Session::has('checkout.plan')) {
            return redirect()->route('checkout.show', [
                'checkout' => Session::get('checkout.plan'),
            ]);
        }

        $organization = GetAppContext::organization();

        $planMetrics = GetPlanMetrics::run();

        $subscription = GetSubscription::run($organization);

        return Inertia::render('Billing/Index', [
            'products' => ListProducts::run(),
            'subscription' => $subscription,
            'trialEnd' => GetTrialEndDate::run($organization),
            'periodEnd' => GetPeriodEndDate::run($organization),
            'subscriptionStatus' => GetSubscriptionStatus::run($organization),
            'usage' => $planMetrics,
            'predictedBill' => PredictNextBill::run($planMetrics, $subscription->plan),
            'invoices' => GetInvoices::run($organization),
        ]);
    }

    public function show(string $billing): SymfonyResponse
    {
        return DownloadInvoice::run(GetAppContext::organization(), $billing);
    }

    public function update(string $billing)
    {
        ResumeSubscription::run(GetAppContext::organization(), $billing);

        return redirect()->route('billing.index')->withAlert('success', 'Your subscription has been resumed successfully.');
    }

    public function destroy(): RedirectResponse
    {
        CancelSubscription::run(GetAppContext::organization());

        return redirect()->route('billing.index')->withAlert('success', 'Your subscription has been cancelled successfully.');
    }
}
