<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Actions\AppContext\GetAppContext;
use App\Actions\FeatureFlags\TouchFeatureFlag;
use App\Actions\Subscriptions\ReportSubscriptionUsage;
use App\Events\FeatureFlagEvaluatedEvent;
use App\Events\FeatureFlagMissedEvent;
use App\Models\Application;
use App\Models\Environment;
use App\Models\FeatureFlagUsage;
use App\Values\FeatureFlag;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class RecordFeatureFlagUsage implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct()
    {
    }

    public function backoff(): array
    {
        return [60, 120, 300];
    }

    public function handle(FeatureFlagEvaluatedEvent|FeatureFlagMissedEvent $event): void
    {
        $application = Application::firstWhere('name', '=', $event->context->appName);
        $environment = Environment::firstWhere('name', '=', $event->context->environment);

        $featureFlag = ($event->featureFlag instanceof FeatureFlag) ? $event->featureFlag : FeatureFlag::from($event->featureFlag);

        // Create a record of this feature flag evaluation
        $usage = FeatureFlagUsage::create([
            'feature_flag_id' => $featureFlag->has('id') && Str::isUlid($featureFlag->id) ? $featureFlag->id : null,
            'feature_flag_name' => $featureFlag->name,
            'application_id' => $application?->id,
            'application_name' => $event->context->appName,
            'environment_id' => $environment?->id,
            'environment_name' => $event->context->environment,
            'active' => $event->response->has('active') ? $event->response->active : false,
            'value' => $event->response->has('value') ? $event->response->value : null,
            'evaluated_at' => Carbon::now(),
        ]);

        if ($featureFlag->has('id') && Str::isUlid($featureFlag->id)) {
            TouchFeatureFlag::run($featureFlag);
        }

        ReportSubscriptionUsage::run($usage->id, GetAppContext::organization());
    }
}
