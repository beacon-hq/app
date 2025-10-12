<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\AppContext\GetAppContext;
use App\Actions\Metrics\GetDashboardMetrics;
use App\Actions\Onboarding\GetOnboardingStatus;
use App\Actions\Subscriptions\GetSubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController
{
    /**
     * Display the dashboard with metrics
     */
    public function index(Request $request): Response
    {
        $dateRange = $request->input('dateRange', null);
        if ($dateRange) {
            $dateRange = [
                'start' => Carbon::parse($dateRange['start']),
                'end' => Carbon::parse($dateRange['end']),
            ];
        }

        $metrics = GetDashboardMetrics::run(GetAppContext::team(), $dateRange);
        $onboardingStatus = GetOnboardingStatus::run();

        if ($onboardingStatus['onboarding'] === true) {
            Cache::forget('dashboard-metrics');
        }

        $plan = null;
        if (config('beacon.billing.enabled', false)) {
            $plan = GetSubscriptionPlan::run(GetAppContext::organization());
        }

        return Inertia::render('Home/Dashboard', [
            ... $onboardingStatus,
            'metrics' => $metrics,
            'plan' => $plan,
        ]);
    }
}
