<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\OrganizationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip rate limiting if billing is not enabled or rate limiting is disabled
        if (!config('beacon.billing.enabled') || !config('beacon.api.rate_limiting.enabled', true)) {
            return $next($request);
        }

        // Apply the 'api' rate limiter
        $executed = RateLimiter::attempt(
            'api',
            1, // Number of attempts to consume
            function () use ($next, $request) {
                return $next($request);
            },
            60 // Time to wait before retrying (seconds)
        );

        if (!$executed) {
            // Get the organization to include in the error message
            $organization = App::context()->organization;

            // Get the organization service from the container
            $organizationService = app(OrganizationService::class);

            // Check if the organization has an active subscription
            $hasActiveSubscription = $organizationService->hasActiveSubscription($organization);

            // Customize the error message based on subscription status
            $message = 'Too many requests. Please try again later.';

            if (!$hasActiveSubscription) {
                $message .= ' Consider upgrading to a paid plan for higher rate limits.';
            } elseif ($organizationService->isTrialSubscription($organization)) {
                $message .= ' Your account is currently on a trial plan with limited API access. Upgrade to a paid plan for higher rate limits.';
            }

            return response()->json([
                'message' => $message,
                'error' => 'rate_limit_exceeded'
            ], 429);
        }

        return $next($request);
    }
}
