<?php

declare(strict_types=1);

namespace App\Listeners;

use App;
use App\Events\TeamCreatedEvent;
use App\Services\FeatureTypeService;
use App\Values\FeatureType;
use Illuminate\Contracts\Queue\ShouldQueue;

class InitTeamDataListener implements ShouldQueue
{
    public function __construct(protected FeatureTypeService $featureTypeService)
    {
    }

    public function handle(TeamCreatedEvent $event): void
    {
        App::context(team: $event->team);

        $featureTypes = collect([
            ['name' => 'Release', 'icon' => 'Rocket', 'color' => 'green', 'description' => 'Manage the rollout of new changes to your application.', 'isDefault' => true, 'tempoary' => true],
            ['name' => 'Operational', 'icon' => 'Wrench', 'color' => 'sky', 'description' => 'Gate functionality based on operational concerns.', 'temporary' => false],
            ['name' => 'Kill Switch', 'icon' => 'Unplug', 'color' => 'red', 'description' => 'Disabling functionality in an emergency.', 'temporary' => false],
            ['name' => 'Experiment', 'icon' => 'FlaskConical', 'color' => 'indigo', 'description' => 'Test new features with a subset of users.', 'temporary' => true],
        ]);

        $featureTypes->each(fn (array $featureType) => $this->featureTypeService->create(FeatureType::from(... $featureType)));
    }
}
