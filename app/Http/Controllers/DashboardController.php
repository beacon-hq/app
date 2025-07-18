<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AppContextService;
use App\Services\MetricsService;
use App\Services\OnboardingService;
use App\Services\SubscriptionBillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with metrics
     */
    public function index(Request $request, MetricsService $dashboardMetricsService, AppContextService $appContextService, SubscriptionBillingService $subscriptionBillingService, OnboardingService $onboardingService): Response
    {
        $dateRange = $request->input('dateRange', null);
        if ($dateRange) {
            $dateRange = [
                'start' => Carbon::parse($dateRange['start']),
                'end' => Carbon::parse($dateRange['end']),
            ];
        }

        $metrics = $dashboardMetricsService->getDashboardMetrics($appContextService->getContext()->team, $dateRange);

        $onboardingStatus = $onboardingService->onboardingStatus();

        if ($onboardingStatus['onboarding'] === true) {
            Cache::forget('dashboard-metrics');
        }

        return Inertia::render('Home/Dashboard', [
            ... $onboardingStatus,
            'metrics' => $metrics,
            'plan' => config('beacon.billing.enabled', false) && $subscriptionBillingService->getPlan($appContextService->getContext()->organization) || null,
        ]);
    }
}
