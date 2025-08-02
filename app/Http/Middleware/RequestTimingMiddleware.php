<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Jobs\RecordRequestTimingMetricJob;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Http\Request;

class RequestTimingMiddleware
{
    protected CarbonImmutable $startTime;

    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    public function terminate(Request $request)
    {
        $shouldSample = (random_int(0, PHP_INT_MAX) / PHP_INT_MAX) <= config('beacon.metrics.request.reporting.sample_rate', 1.0);
        if (config('beacon.metrics.request.enabled') && config('beacon.metrics.request.reporting.enabled') && $shouldSample) {
            $requestDuration = CarbonImmutable::createFromFormat('U.u', (string) $request->server->get('REQUEST_TIME_FLOAT'))->diffInMilliseconds(now());

            RecordRequestTimingMetricJob::dispatch($request, $requestDuration);
        }
    }
}
