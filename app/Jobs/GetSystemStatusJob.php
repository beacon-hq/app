<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GetSystemStatusJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function handle(): void
    {
        if (!config('beacon.status.enabled')) {
            return;
        }

        try {
            $json = Http::get(config('services.cachet.url') . '/api/status')
                ->throw()
                ->json();

            Cache::put(
                'beacon.status',
                $json,
            );
        } catch (HttpClientException $e) {
            Log::error('Failed to fetch system status', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
