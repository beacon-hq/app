<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Actions\Applications\CreateApplication;
use App\Actions\Applications\FindApplicationByName;
use App\Actions\Environments\CreateEnvironment;
use App\Actions\Environments\FindEnvironmentByName;
use App\Actions\FeatureFlags\CreateFeatureFlag;
use App\Actions\FeatureFlags\FindFeatureFlagByName;
use App\Actions\FeatureFlags\UpdateFeatureFlag;
use App\Actions\FeatureTypes\GetDefaultFeatureType;
use App\Events\FeatureFlagEvaluatedEvent;
use App\Events\FeatureFlagMissedEvent;
use App\Values\Application;
use App\Values\Collections\FeatureFlagStatusCollection;
use App\Values\Environment;
use App\Values\FeatureFlag;
use App\Values\FeatureFlagStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;

class CreateMissingFeatureFlagListener implements ShouldQueue
{
    public function handle(FeatureFlagMissedEvent $event): void
    {
        $flag = $event->featureFlag;

        $colors = [
            'red',
            'orange',
            'yellow',
            'lime',
            'green',
            'emerald',
            'cyan',
            'sky',
            'blue',
            'indigo',
            'purple',
            'fuchsia',
        ];

        $application = FindApplicationByName::run($event->context->appName);
        if ($application === null) {
            $application = CreateApplication::run(Application::from(name: $event->context->appName, displayName: $event->context->appName, color: Arr::random($colors)));
        }

        if (($environment = FindEnvironmentByName::run($event->context->environment)) === null) {
            $environment = CreateEnvironment::run(Environment::from(name: $event->context->environment, color: Arr::random($colors)));
        }

        try {
            $flag = FindFeatureFlagByName::run($flag->name);
        } catch (ModelNotFoundException) {
            $featureType = null;

            try {
                $featureType = GetDefaultFeatureType::run();
            } catch (ModelNotFoundException) {
            }
            $flag = CreateFeatureFlag::run(FeatureFlag::from(name: $flag->name, featureType: $featureType));
        }

        // Check if a status already exists for this application and environment
        $existingStatus = $flag->statuses->first(function ($status) use ($application, $environment) {
            return $status->application->id === $application->id && $status->environment->id === $environment->id;
        });

        if (!$existingStatus) {
            $flag = $flag->with(statuses: FeatureFlagStatusCollection::make([
                ... $flag->statuses->all(),
                FeatureFlagStatus::from(application: $application, environment: $environment, status: false)
            ]));
        }

        UpdateFeatureFlag::run($flag);

        FeatureFlagEvaluatedEvent::dispatch($flag, $event->context, $event->response);
    }
}
