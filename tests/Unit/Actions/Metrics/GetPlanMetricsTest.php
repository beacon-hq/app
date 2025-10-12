<?php

declare(strict_types=1);

use App\Actions\AppContext\SetTeamContext;
use App\Actions\Metrics\GetPlanMetrics;
use App\Models\Application;
use App\Models\Environment;
use App\Models\FeatureFlag;
use App\Models\FeatureFlagUsage;
use App\Models\Team;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Carbon::setTestNow('2025-09-15 12:00:00');
});

it('computes plan evaluation metrics for current and previous month and projection with complete daily data', function () {
    Cache::forget('plan-metrics');

    $team = Team::factory()->create();

    $application = Application::factory()->for($team)->create();
    $environment = Environment::factory()->for($team)->create();
    $featureFlag = FeatureFlag::factory()->for($team)->create();

    FeatureFlagUsage::factory(2)
        ->for($team)
        ->for($application)
        ->for($environment)
        ->for($featureFlag)
        ->create([
            'evaluated_at' => Carbon::now(),
        ]);

    FeatureFlagUsage::factory(4)
        ->for($team)
        ->for($application)
        ->for($environment)
        ->for($featureFlag)
        ->create([
            'evaluated_at' => Carbon::now()->startOfMonth(),
        ]);

    FeatureFlagUsage::factory(3)
        ->for($team)
        ->for($application)
        ->for($environment)
        ->for($featureFlag)
        ->create(attributes: [
            'evaluated_at' => Carbon::now()->endOfMonth()
        ]);

    FeatureFlagUsage::factory(7)
        ->for($team)
        ->for($application)
        ->for($environment)
        ->for($featureFlag)
        ->create([
            'evaluated_at' => Carbon::now()->subMonth(),
        ]);

    SetTeamContext::run($team);

    $metrics = GetPlanMetrics::run();

    expect($metrics)->toBeArray()->toHaveKey('evaluations');

    $evaluations = $metrics['evaluations'];

    expect($evaluations)
        ->toHaveKeys(['currentMonth', 'previousMonth', 'projected', 'dailyData'])
        ->and($evaluations['currentMonth'])->toBe(6)
        ->and($evaluations['previousMonth'])->toBe(7)
        ->and($evaluations['projected'])->toBe(11);

    $dailyData = $evaluations['dailyData'];

    expect($dailyData)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(15)
        ->and($dailyData->toArray())
        ->toBe([
            '2025-09-01' => 4,
            '2025-09-02' => 0,
            '2025-09-03' => 0,
            '2025-09-04' => 0,
            '2025-09-05' => 0,
            '2025-09-06' => 0,
            '2025-09-07' => 0,
            '2025-09-08' => 0,
            '2025-09-09' => 0,
            '2025-09-10' => 0,
            '2025-09-11' => 0,
            '2025-09-12' => 0,
            '2025-09-13' => 0,
            '2025-09-14' => 0,
            '2025-09-15' => 2,
        ]);

});

it('returns cached plan metrics when present', function () {
    $cached = [
        'evaluations' => [
            'currentMonth' => 123,
            'previousMonth' => 45,
            'projected' => 246,
            'dailyData' => collect(['2025-09-01' => 10]),
        ],
    ];

    Cache::spy()->shouldReceive('flexible')->andReturn($cached);

    $metrics = GetPlanMetrics::run();

    expect($metrics)->toEqual($cached);
});
