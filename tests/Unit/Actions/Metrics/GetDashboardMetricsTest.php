<?php

declare(strict_types=1);

use App\Actions\Metrics\GetDashboardMetrics;
use App\Events\TeamCreatedEvent;
use App\Models\Activity;
use App\Models\Application;
use App\Models\Environment;
use App\Models\FeatureFlag;
use App\Models\FeatureFlagStatus;
use App\Models\FeatureFlagUsage;
use App\Models\FeatureType;
use App\Models\Tag;
use App\Models\User;
use Beacon\Metrics\Enums\PreviousType;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\Unit\Actions\AppContext\SetAppContext;

beforeEach(function () {
    \Event::fake([TeamCreatedEvent::class]);

    Carbon::setTestNow('2025-09-25 01:17:00');

    [$this->team, $this->user, $this->organization] = createOwner();

    SetAppContext::run($this->organization, $this->team);

    $this->featureTypeA = FeatureType::factory()->for($this->team)->temporary()->create([
        'name' => 'Type A',
        'color' => '#ff0000',
        'icon' => 'Flag',
    ]);

    $this->featureTypeB = FeatureType::factory()->for($this->team)->temporary()->create([
        'name' => 'Type B',
        'color' => '#00ff00',
        'icon' => 'Bell',
    ]);

    $this->application = Application::factory()->for($this->team)->create([
        'name' => 'test-app',
        'display_name' => 'Test App',
        'color' => '#41d855',
    ]);

    $this->environment = Environment::factory()->for($this->team)->create([
        'name' => 'test-env',
        'description' => 'Test Environment',
    ]);

    $this->tagA = Tag::factory()->for($this->team)->create([
        'name' => 'Tag A',
        'color' => '#ff0000',
    ]);

    $this->tagB = Tag::factory()->for($this->team)->create([
        'name' => 'Tag B',
        'color' => '#00ff00',
    ]);

    $this->activeFlag1 = FeatureFlag::factory()
        ->for($this->team)
        ->for($this->featureTypeA, 'featureType')
        ->active()
        ->create([
            'name' => 'active-flag-1',
            'description' => 'Active flag 1',
            'created_at' => now()->subDays(10),
        ]);

    $this->activeFlag1->tags()->attach($this->tagA->id);

    $this->activeFlag1->statuses()->attach(FeatureFlagStatus::factory()
        ->active()
        ->for($this->activeFlag1)
        ->for($this->application)
        ->for($this->environment)
        ->create());

    $this->inactiveFlag1 = FeatureFlag::factory()
        ->for($this->team)
        ->for($this->featureTypeB, 'featureType')
        ->inactive()
        ->create([
            'name' => 'inactive-flag-1',
            'description' => 'Inactive flag 1',
            'created_at' => now()->subMonths(2),
        ]);

    $this->inactiveFlag1->tags()->attach($this->tagB->id);

    $this->inactiveFlag1->statuses()->attach(FeatureFlagStatus::factory()
        ->active()
        ->for($this->inactiveFlag1)
        ->for($this->application)
        ->for($this->environment)
        ->create());

    $this->inactiveFlag2 = FeatureFlag::factory()
        ->for($this->team)
        ->for($this->featureTypeB, 'featureType')
        ->active()
        ->create([
            'name' => 'inactive-flag-2',
            'description' => 'Inactive flag 2',
            'created_at' => now()->subMonths(2),
        ]);

    $this->inactiveFlag2->statuses()->attach(FeatureFlagStatus::factory()
        ->inactive()
        ->for($this->inactiveFlag2)
        ->for($this->application)
        ->for($this->environment)
        ->create());

    $this->archivedFlag1 = FeatureFlag::factory()
        ->for($this->team)
        ->for($this->featureTypeA, 'featureType')
        ->inactive()
        ->create([
            'name' => 'archived-flag-1',
            'description' => 'Archived flag 1',
            'created_at' => now()->subMonths(7),
            'completed_at' => now()->subDays(3),
        ]);

    $this->recentFlag1 = FeatureFlag::factory()
        ->for($this->team)
        ->for($this->featureTypeA, 'featureType')
        ->active()
        ->create([
            'name' => 'recent-flag-1',
            'description' => 'Recent flag 1',
            'created_at' => now()->subDays(3),
        ]);

    $this->recentFlag1->statuses()->attach(FeatureFlagStatus::factory()
        ->active()
        ->for($this->recentFlag1)
        ->for($this->application)
        ->for($this->environment)
        ->create());

    // Create today's flags
    $this->todayFlag1 = FeatureFlag::factory()
        ->for($this->team)
        ->for($this->featureTypeB, 'featureType')
        ->active()
        ->create([
            'name' => 'today-flag-1',
            'description' => 'Today flag 1',
            'created_at' => now(),
        ]);

    $this->todayFlag1->statuses()->attach(FeatureFlagStatus::factory()
        ->active()
        ->for($this->todayFlag1)
        ->for($this->application)
        ->for($this->environment)
        ->create());

    // Create activity logs directly
    $activity1 = new Activity();
    $activity1->log_name = 'default';
    $activity1->description = 'updated';
    $activity1->subject_type = 'App\\Models\\FeatureFlag';
    $activity1->subject_id = $this->activeFlag1->id;
    $activity1->causer_type = User::class;
    $activity1->causer_id = $this->user->id;
    $activity1->properties = json_encode([]);
    $activity1->created_at = now()->subDays(5);
    $activity1->team_id = $this->team->id;
    $activity1->save();

    $activity2 = new Activity();
    $activity2->log_name = 'default';
    $activity2->description = 'updated';
    $activity2->subject_type = 'App\\Models\\FeatureFlag';
    $activity2->subject_id = $this->inactiveFlag1->id;
    $activity2->causer_type = User::class;
    $activity2->causer_id = $this->user->id;
    $activity2->properties = json_encode([]);
    $activity2->created_at = now();
    $activity2->team_id = $this->team->id;
    $activity2->save();

    $this->getDashboardMetricsAction = GetDashboardMetrics::make();
});

it('returns all required metrics in the expected structure', function () {
    // Clear any existing cache before testing
    Cache::forget('dashboard-metrics');

    $metrics = $this->getDashboardMetricsAction->handle();

    // Check overall structure
    expect($metrics)->toBeArray()
        ->toHaveKeys([
            'totalFlags',
            'changesMetrics',
            'createdMetrics',
            'completedMetrics',
            'flagStatusData',
            'flagTypeData',
            'ageData',
            'usageOverTimeData',
            'usageData',
            'oldestData',
        ]);
});

it('returns the correct total number of flags', function () {
    Cache::forget('dashboard-metrics');

    $metrics = $this->getDashboardMetricsAction->handle();

    // We created 5 feature flags in total
    expect($metrics['totalFlags'])->toBe([
        'value' => 5,
        'previous' => [
            'difference' => 0,
            'type' => 'increase',
        ]
    ]);
});

it('returns correct changes metrics', function () {
    Cache::forget('dashboard-metrics');

    $metrics = $this->getDashboardMetricsAction->handle();

    expect($metrics['changesMetrics'])
        ->toBeArray()
        ->toHaveKeys(['value', 'previous'])
        ->and($metrics['changesMetrics']['value'])->toBe(2)
        ->and($metrics['changesMetrics']['previous'])->toBe([
            'type' => PreviousType::INCREASE,
            'value' => 0,
            'difference' => 2,
            'percentage' => 100,
        ]);

});

it('returns correct created metrics', function () {
    Cache::forget('dashboard-metrics');

    $metrics = $this->getDashboardMetricsAction->handle();

    expect($metrics['createdMetrics'])
        ->toBeArray()
        ->toHaveKeys(['value', 'previous'])
        ->and($metrics['createdMetrics']['value'])->toBe(3)
        ->and($metrics['createdMetrics']['previous'])->toBe([
            'type' => PreviousType::INCREASE,
            'value' => 0,
            'difference' => 3,
            'percentage' => 100,
        ]);

});

it('returns correct archived metrics', function () {
    Cache::forget('dashboard-metrics');

    $metrics = $this->getDashboardMetricsAction->handle();

    expect($metrics['completedMetrics'])
        ->toBeArray()
        ->toHaveKeys(['value', 'previous'])
        ->and($metrics['completedMetrics']['value'])->toBe(1)
        ->and($metrics['completedMetrics']['previous'])->toBe([
            'type' => PreviousType::INCREASE,
            'value' => 0,
            'difference' => 1,
            'percentage' => 100,
        ]);

});

it('returns correct flag lifecycle metrics', function () {
    Cache::forget('dashboard-metrics');

    $metrics = $this->getDashboardMetricsAction->handle();

    expect($metrics['flagStatusData'])
        ->toBeArray()
        ->toBe([
            [
                'state' => 'unused',
                'flags' => 3,
                'fill' => 'var(--color-unused)',
            ],
            [
                'state' => 'active',
                'flags' => 3,
                'fill' => 'var(--color-active)',
            ],
            [
                'state' => 'stale',
                'flags' => 0,
                'fill' => 'var(--color-stale)',
            ],
            [
                'state' => 'inactive',
                'flags' => 2,
                'fill' => 'var(--color-inactive)',
            ],
        ]);

});

it('returns correct flag type data', function () {
    Cache::forget('dashboard-metrics');

    $metrics = $this->getDashboardMetricsAction->handle();

    expect($metrics['flagTypeData'])
        ->toBeArray()
        ->toHaveKeys(['data', 'total'])
        ->toBe([
            'data' => [
                [
                    'flags' => 3,
                    'id' => $this->featureTypeA->id,
                    'type' => 'Type A',
                    'fill' => '#ff0000',
                ],
                [
                    'flags' => 3,
                    'id' => $this->featureTypeB->id,
                    'type' => 'Type B',
                    'fill' => '#00ff00',
                ],
            ],
            'total' => 6,
        ]);
});

it('returns correct age data', function () {
    Cache::forget('dashboard-metrics');

    $metrics = $this->getDashboardMetricsAction->handle();

    expect($metrics['ageData'])
        ->toBeArray()
        ->toBe([
            [
                'month' => 'April',
                'age' => 63,
            ],
            [
                'month' => 'May',
                'age' => 94,
            ],
            [
                'month' => 'June',
                'age' => 124,
            ],
            [
                'month' => 'July',
                'age' => 55,
            ],
            [
                'month' => 'August',
                'age' => 86,
            ],
            [
                'month' => 'September',
                'age' => 63,
            ],
        ]);
});

it('returns usage over time data for the last 30 days', function () {
    Cache::forget('dashboard-metrics');

    FeatureFlagUsage::factory()
        ->count(100)
        ->for($this->application)
        ->for($this->environment)
        ->for($this->team)
        ->state(new Sequence(['active' => true], ['active' => false]))
        ->state(new Sequence(
            ['feature_flag_id' => $this->activeFlag1->id, 'feature_flag_name' => $this->activeFlag1->name],
            ['feature_flag_id' => $this->inactiveFlag1->id, 'feature_flag_name' => $this->inactiveFlag1->name],
            ['feature_flag_id' => $this->inactiveFlag2->id, 'feature_flag_name' => $this->inactiveFlag2->name],
        ))
        ->state(new Sequence(fn (Sequence $sequence) => [
            'evaluated_at' => now()->subDays($sequence->index),
        ]))
        ->create([
            'application_name' => $this->application->name,
            'environment_name' => $this->environment->name,
        ]);

    $metrics = $this->getDashboardMetricsAction->handle();

    expect($metrics['usageOverTimeData'])
        ->toBeArray()
        ->and($metrics['usageOverTimeData'])
        ->toHaveCount(6)
        ->toBe([
            [
                'month' => 'April',
                'active' => 0,
                'inactive' => 0,
            ],
            [
                'month' => 'May',
                'active' => 0,
                'inactive' => 0,
            ],
            [
                'month' => 'June',
                'active' => 6,
                'inactive' => 7,
            ],
            [
                'month' => 'July',
                'active' => 16,
                'inactive' => 15,
            ],
            [
                'month' => 'August',
                'active' => 15,
                'inactive' => 16,
            ],
            [
                'month' => 'September',
                'active' => 13,
                'inactive' => 12,
            ]
        ]);
});

it('returns top usage data', function () {
    Cache::forget('dashboard-metrics');

    SetAppContext::run($this->team->organization, $this->team);

    FeatureFlagUsage::factory(150)
        ->for($this->activeFlag1)
        ->for($this->team)
        ->for($this->application)
        ->for($this->environment)
        ->create([
            'evaluated_at' => now()->subDays(1),
        ]);

    FeatureFlagUsage::factory(20)
        ->for($this->inactiveFlag1)
        ->for($this->team)
        ->for($this->application)
        ->for($this->environment)
        ->create([
            'evaluated_at' => now()->subDays(1),
        ]);

    SetAppContext::run($this->team->organization, $this->team);

    $metrics = $this->getDashboardMetricsAction->handle();

    expect($metrics['usageData'])
        ->toBeArray()
        ->toBe([
            [
                'name' => 'active-flag-1',
                'application' => [
                    'name' => $this->application->name,
                    'color' => $this->application->color,
                ],
                'feature_type' => [
                    'color' => $this->featureTypeA->color,
                    'icon' => $this->featureTypeA->icon,
                ],
                'usages' => 150,
            ],
            [
                'name' => 'inactive-flag-1',
                'application' => [
                    'name' => $this->application->name,
                    'color' => $this->application->color,
                ],
                'feature_type' => [
                    'color' => $this->featureTypeB->color,
                    'icon' => $this->featureTypeB->icon,
                ],
                'usages' => 20,
            ],
        ]);
});

it('returns oldest flags data', function () {
    Cache::forget('dashboard-metrics');

    $metrics = $this->getDashboardMetricsAction->handle();

    expect($metrics['oldestData'])
        ->toBeArray()
        ->toBe(
            [
                [
                    'name' => 'active-flag-1',
                    'application' => [
                        'name' => 'test-app',
                        'color' => '#41d855',
                    ],
                    'feature_type' => [
                        'color' => '#ff0000',
                        'icon' => 'Flag',
                    ],
                    'created_at' => '2025-09-25 01:17:00',
                ],
                [
                    'name' => 'inactive-flag-1',
                    'application' => [
                        'name' => 'test-app',
                        'color' => '#41d855',
                    ],
                    'feature_type' => [
                        'color' => '#00ff00',
                        'icon' => 'Bell',
                    ],
                    'created_at' => '2025-09-25 01:17:00',
                ],
                [
                    'name' => 'inactive-flag-2',
                    'application' => [
                        'name' => 'test-app',
                        'color' => '#41d855',
                    ],
                    'feature_type' => [
                        'color' => '#00ff00',
                        'icon' => 'Bell',
                    ],
                    'created_at' => '2025-09-25 01:17:00',
                ],
                [
                    'name' => 'recent-flag-1',
                    'application' => [
                        'name' => 'test-app',
                        'color' => '#41d855',
                    ],
                    'feature_type' => [
                        'color' => '#ff0000',
                        'icon' => 'Flag',
                    ],
                    'created_at' => '2025-09-25 01:17:00',
                ],
                [
                    'name' => 'today-flag-1',
                    'application' => [
                        'name' => 'test-app',
                        'color' => '#41d855',
                    ],
                    'feature_type' => [
                        'color' => '#00ff00',
                        'icon' => 'Bell',
                    ],
                    'created_at' => '2025-09-25 01:17:00',
                ],
            ]
        );

});

it('returns cached metrics when available', function () {
    $metrics1 = $this->getDashboardMetricsAction->handle();

    FeatureFlag::factory()
        ->for($this->team)
        ->for($this->featureTypeA, 'featureType')
        ->create([
            'name' => 'new-flag-after-cache',
            'description' => 'New flag after cache',
            'created_at' => now(),
        ]);

    $metrics2 = $this->getDashboardMetricsAction->handle();

    expect($metrics2['totalFlags'])
        ->toBe($metrics1['totalFlags'])
        ->and($metrics2['totalFlags'])->toBe([
            'value' => 5,
            'previous' => [
                'difference' => 0,
                'type' => 'increase',
            ]
        ]);

    Cache::forget('dashboard-metrics');
    $metrics3 = $this->getDashboardMetricsAction->handle();

    expect($metrics3['totalFlags'])->toBe([
        'value' => 6,
        'previous' => [
            'difference' => 0,
            'type' => 'increase',
        ]
    ]);
});
