<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\FeatureFlagEvaluatedEvent;
use App\Events\FeatureFlagMissedEvent;
use App\Services\ApplicationService;
use App\Services\EnvironmentService;
use App\Services\FeatureFlagService;
use App\Services\FeatureFlagStatusService;
use App\Services\FeatureTypeService;
use App\Values\Application;
use App\Values\Collections\FeatureFlagStatusCollection;
use App\Values\Environment;
use App\Values\FeatureFlag;
use App\Values\FeatureFlagStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;

class CreateMissingFeatureFlagListener implements ShouldQueue
{
    public function __construct(
        protected ApplicationService $applicationService,
        protected EnvironmentService $environmentService,
        protected FeatureFlagService $featureFlagService,
        protected FeatureFlagStatusService $featureFlagStatusService,
        protected FeatureTypeService $featureTypeService,
    ) {
    }

    public function handle(FeatureFlagMissedEvent $event): void
    {
        App::context(organization: $event->appContext->organization, team: $event->appContext->team);

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

        if (($application = $this->applicationService->findByName($event->context->appName)) === null) {
            $application = $this->applicationService->create(Application::from(name: $event->context->appName, displayName: $event->context->appName, color: Arr::random($colors)));
        }

        if (($environment = $this->environmentService->findByName($event->context->environment)) === null) {
            $environment = $this->environmentService->create(Environment::from(name: $event->context->environment, color: Arr::random($colors)));
        }

        try {
            $flag = $this->featureFlagService->findByName($flag->name);
        } catch (ModelNotFoundException) {
            $featureType = null;

            try {
                $featureType = $this->featureTypeService->getDefault();
            } catch (ModelNotFoundException) {
            }
            $flag = $this->featureFlagService->create(FeatureFlag::from(name: $flag->name, featureType: $featureType));
        }

        $flag = $flag->with(statuses: FeatureFlagStatusCollection::make([
            ... $flag->statuses->all(),
            FeatureFlagStatus::from(application: $application, environment: $environment, status: false)
        ]));

        $this->featureFlagService->update($flag);

        FeatureFlagEvaluatedEvent::dispatch($flag, $event->context, $event->response);
    }
}
