<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AppContextService;
use App\Services\MetricsService;
use App\Services\SubscriptionBillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with metrics
     */
    public function index(Request $request, MetricsService $dashboardMetricsService, AppContextService $appContextService, SubscriptionBillingService $subscriptionBillingService): Response
    {
        $dateRange = $request->input('dateRange', null);
        if ($dateRange) {
            $dateRange = [
                'start' => Carbon::parse($dateRange['start']),
                'end' => Carbon::parse($dateRange['end']),
            ];
        }

        $metrics = $dashboardMetricsService->getDashboardMetrics($appContextService->getContext()->team, $dateRange);

        return Inertia::render('Home/Dashboard', [
            'metrics' => $metrics,
            'plan' => $subscriptionBillingService->getPlan($appContextService->getContext()->organization),
        ]);
    }
}
