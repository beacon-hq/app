<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App;
use Closure;
use Illuminate\Http\Request;
use Session;

class EnsureSubscriptionMiddleware
{
    public function __construct(protected App\Services\SubscriptionBillingService $subscriptionBillingService)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        if (!config('beacon.billing.enabled')) {
            return $next($request);
        }

        if (auth()->hasUser()) {
            if ($request->routeIs('checkout.*', 'welcome', 'logout')) {
                return $next($request);
            }

            if ($request->has('plan')) {
                Session::put('checkout.plan', $request->string('plan'));
            }

            if ($this->subscriptionBillingService->hasActiveSubscription(App::context()->organization)) {
                return $next($request);
            } else {
                if (Session::has('checkout.plan')) {
                    return redirect()->route('checkout.show', [
                        'checkout' => Session::get('checkout.plan'),
                    ]);
                }

                return redirect()->route('checkout.index');
            }
        }

        return $next($request);
    }
}
