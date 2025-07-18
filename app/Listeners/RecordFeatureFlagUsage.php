<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\FeatureFlagEvaluatedEvent;
use App\Events\FeatureFlagMissedEvent;
use App\Models\Application;
use App\Models\Environment;
use App\Models\FeatureFlagUsage;
use App\Services\FeatureFlagService;
use App\Services\SubscriptionBillingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RecordFeatureFlagUsage implements ShouldQueue
{
    use Queueable;

    public function __construct(protected FeatureFlagService $featureFlagService, protected SubscriptionBillingService $subscriptionBillingService)
    {
    }

    public function handle(FeatureFlagEvaluatedEvent|FeatureFlagMissedEvent $event): void
    {
        try {
            App::context(team: $event->appContext->team, organization: $event->appContext->organization);

            $application = Application::firstWhere('name', '=', $event->context->appName);
            $environment = Environment::firstWhere('name', '=', $event->context->environment);

            // Create a record of this feature flag evaluation
            $usage = FeatureFlagUsage::create([
                'feature_flag_id' => $event->featureFlag->has('id') && Str::isUlid($event->featureFlag->id) ? $event->featureFlag->id : null,
                'feature_flag_name' => $event->featureFlag->name,
                'application_id' => $application?->id,
                'application_name' => $event->context->appName,
                'environment_id' => $environment?->id,
                'environment_name' => $event->context->environment,
                'active' => $event->response->has('active') ? $event->response->active : false,
                'value' => $event->response->has('value') ? $event->response->value : null,
                'evaluated_at' => Carbon::now(),
            ]);

            if ($event->featureFlag->has('id') && Str::isUlid($event->featureFlag->id)) {
                $this->featureFlagService->touch($event->featureFlag);
            }

            $this->subscriptionBillingService->reportUsage($usage->id, $event->appContext->organization);
        } catch (\Throwable $e) {
            // Log the exception but don't fail the application
            Log::error('Failed to record feature flag usage: ' . $e->getMessage(), [
                'exception' => $e,
                'feature_flag' => $event->featureFlag->id,
            ]);
        }
    }
}
