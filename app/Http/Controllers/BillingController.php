<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\AppContext\GetAppContext;
use App\Actions\Metrics\GetPlanMetrics;
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
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class BillingController
{
    public function index(
        GetProducts $getProductsAction,
        GetPlanMetrics $getPlanMetricsAction,
        GetSubscription $getSubscriptionAction,
        GetTrialEndDate $getTrialEndDateAction,
        GetPeriodEndDate $getPeriodEndDateAction,
        GetSubscriptionStatus $getSubscriptionStatusAction,
        PredictNextBill $predictNextBillAction,
        GetInvoices $getInvoicesAction
    ): Response {
        $organization = GetAppContext::organization();

        $planMetrics = $getPlanMetricsAction->handle();

        $subscription = $getSubscriptionAction->handle($organization);

        return Inertia::render('Billing/Index', [
            'products' => $getProductsAction->handle(),
            'subscription' => $subscription,
            'trialEnd' => $getTrialEndDateAction->handle($organization),
            'periodEnd' => $getPeriodEndDateAction->handle($organization),
            'subscriptionStatus' => $getSubscriptionStatusAction->handle($organization),
            'usage' => $planMetrics,
            'predictedBill' => $predictNextBillAction->handle($planMetrics, $subscription->plan),
            'invoices' => $getInvoicesAction->handle($organization),
        ]);
    }

    public function show(string $billing, DownloadInvoice $downloadInvoiceAction): SymfonyResponse
    {
        return $downloadInvoiceAction->handle(GetAppContext::organization(), $billing);
    }

    public function update(string $billing, ResumeSubscription $resumeSubscriptionAction)
    {
        $resumeSubscriptionAction->handle(GetAppContext::organization(), $billing);

        return redirect()->route('billing.index')->withAlert('success', 'Your subscription has been resumed successfully.');
    }

    public function destroy(CancelSubscription $cancelSubscriptionAction): RedirectResponse
    {
        $cancelSubscriptionAction->handle(GetAppContext::organization());

        return redirect()->route('billing.index')->withAlert('success', 'Your subscription has been cancelled successfully.');
    }
}
