<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Features\RequestMetrics;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Pennant\Feature;

class RecordRequestTimingMetricJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected string $requestType;

    public function __construct(Request $request, public float $requestDuration)
    {
        $this->requestType = Str::startsWith($request->path(), '/api/') ? 'api' : 'web';
    }

    public function handle(): void
    {
        if (!Feature::driver('array')->active(RequestMetrics::class)) {
            return;
        }

        $metric = config('services.cachet.metrics.web_request_timing');
        if ($this->requestType === 'api') {
            $metric = config('services.cachet.metrics.api_request_timing');
        }

        Http::withToken(config('services.cachet.token'))
            ->post(
                config('services.cachet.url') . "/api/metrics/$metric/points",
                [
                    'value' => $this->requestDuration,
                ]
            );
    }
}
