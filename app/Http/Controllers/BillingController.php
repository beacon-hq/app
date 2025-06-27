<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Services\MetricsService;
use App\Services\ProductService;
use App\Services\SubscriptionBillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    public function __construct(protected SubscriptionBillingService $subscriptionBillingService)
    {
    }

    public function index(ProductService $productService, MetricsService $metricsService): Response
    {
        $organization = App::context()->organization;

        $planMetrics = $metricsService->getPlanMetrics();

        $subscription = $this->subscriptionBillingService->getSubscription($organization);

        return Inertia::render('Billing/Index', [
            'products' => $productService->all(),
            'subscription' => $subscription,
            'trialEnd' => $this->subscriptionBillingService->getTrialEndDate($organization),
            'periodEnd' => $this->subscriptionBillingService->getPeriodEndDate($organization),
            'subscriptionStatus' => $this->subscriptionBillingService->getSubscriptionStatus($organization),
            'usage' => $planMetrics,
            'predictedBill' => $this->subscriptionBillingService->predictNextBill($planMetrics, $subscription->plan),
            'invoices' => $this->subscriptionBillingService->getInvoices($organization),
        ]);
    }

    public function show(string $billing): \Symfony\Component\HttpFoundation\Response
    {
        return Organization::find(App::context()->organization->id)->downloadInvoice($billing);
    }

    public function update(string $billing)
    {
        $this->subscriptionBillingService->resumeSubscription(App::context()->organization, $billing);

        return redirect()->route('billing.index')->withAlert('success', 'Your subscription has been resumed successfully.');
    }

    public function destroy(): RedirectResponse
    {
        $this->subscriptionBillingService->cancelSubscription(App::context()->organization);

        return redirect()->route('billing.index')->withAlert('success', 'Your subscription has been cancelled successfully.');
    }
}
