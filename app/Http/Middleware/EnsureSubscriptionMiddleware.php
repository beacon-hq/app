<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Actions\Subscriptions\HasActiveSubscription;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Session;

class EnsureSubscriptionMiddleware
{
    public function __construct(protected HasActiveSubscription $hasActiveSubscriptionAction)
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

            if (Context::has('organization') && $this->hasActiveSubscriptionAction->handle(Context::get('organization'))) {
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
