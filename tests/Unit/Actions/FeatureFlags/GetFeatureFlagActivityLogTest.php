<?php

declare(strict_types=1);

use App\Actions\AppContext\SetAppContext;
use App\Actions\FeatureFlags\CreateFeatureFlag;
use App\Actions\FeatureFlags\GetFeatureFlagActivityLog;
use App\Actions\FeatureFlags\UpdateFeatureFlag;
use App\Models\Application;
use App\Models\Environment;
use App\Models\FeatureType;
use App\Values\FeatureFlag;
use App\Values\FeatureType as FeatureTypeValue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use function Pest\Laravel\travel;

it('retrieves activity logs for a feature flag', function () {
    Config::set('activitylog.enabled', true);

    [$team, , $organization] = createOwner();

    SetAppContext::run($organization, $team);

    $flag = CreateFeatureFlag::run(FeatureFlag::from([
        'name' => 'test-flag',
        'feature_type' => FeatureType::factory()->for($team)->create(),
    ]));

    travel(30)->seconds();

    $flag = $flag->with(featureType: FeatureTypeValue::from(FeatureType::factory()->for($team)->create()));
    UpdateFeatureFlag::run($flag);

    $log = GetFeatureFlagActivityLog::run($flag->id);
    expect($log)
        ->toBeInstanceOf(Collection::class)
        ->and($log->count())->toBe(2)
        ->and($log->first()->event)->toBe('updated')
        ->and($log->last()->event)->toBe('created');
});

it('includes both feature flag and feature flag status activities', function () {
    Config::set('activitylog.enabled', true);

    [$team, $user] = createOwner();

    SetAppContext::run($team->organization, $team);

    $flag = CreateFeatureFlag::run(FeatureFlag::from([
        'name' => 'test-flag',
        'feature_type' => FeatureType::factory()->for($team)->create(),
    ]));

    travel(30)->seconds();

    $flag = $flag->with(statuses: collect([[
        'environment' => Environment::factory()->for($team)->create(),
        'application' => Application::factory()->for($team)->create(),
        'status' => true,
    ]]));
    UpdateFeatureFlag::run($flag);

    $log = GetFeatureFlagActivityLog::run($flag->id);
    expect($log)
        ->toBeInstanceOf(Collection::class)
        ->and($log->first()->event)->toBe('created')
        ->and($log->first()->subject)->toContain('>')
        ->and($log->last()->event)->toBe('created')
        ->and($log->last()->subject)->toBe('')
        ->and($log->count())->toBe(2);
});

it('throws a model not found exception for non-existent feature flag', function () {
    GetFeatureFlagActivityLog::run('bad-flag');
})->throws(ModelNotFoundException::class);
