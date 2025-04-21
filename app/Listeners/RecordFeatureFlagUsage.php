<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\FeatureFlagEvaluated;
use App\Models\Application;
use App\Models\Environment;
use App\Models\FeatureFlagUsage;
use App\Services\FeatureFlagService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class RecordFeatureFlagUsage implements ShouldQueue
{
    use Queueable;

    public function __construct(protected FeatureFlagService $featureFlagService)
    {
    }

    public function handle(FeatureFlagEvaluated $event): void
    {
        try {
            App::context(team: $event->appContext->team, organization: $event->appContext->organization);

            // Find or create application and environment records if they don't exist
            $application = Application::firstOrCreate(['name' => $event->context->appName]);
            $environment = Environment::firstOrCreate(['name' => $event->context->environment]);

            // Create a record of this feature flag evaluation
            FeatureFlagUsage::create([
                'feature_flag_id' => $event->featureFlag->id,
                'application_id' => $application->id,
                'environment_id' => $environment->id,
                'active' => $event->response->active,
                'value' => $event->response->value,
                'evaluated_at' => Carbon::now(),
            ]);

            $this->featureFlagService->touch($event->featureFlag);
        } catch (\Exception $e) {
            // Log the exception but don't fail the application
            Log::error('Failed to record feature flag usage: ' . $e->getMessage(), [
                'exception' => $e,
                'feature_flag' => $event->featureFlag->id,
            ]);
        }
    }
}
