<?php

declare(strict_types=1);

use App\Enums\PolicyDefinitionType;
use App\Repositories\FeatureFlagStatusRepository;
use App\Services\PolicyService;
use App\Values\PolicyDefinition;
use Bag\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Date;

covers(FeatureFlagStatusRepository::class);

it('evaluates date range policy type correctly', function () {
    // Mock the current date to a fixed value for testing
    $now = Carbon::create(2023, 6, 15, 12, 0, 0);
    Date::setTestNow($now);

    try {
        // Create a repository instance with a mock PolicyService
        $policyService = \Mockery::mock(PolicyService::class);
        $repository = new FeatureFlagStatusRepository($policyService);

        // Test case 1: Current date is within the date range (should return true)
        $startDate = Carbon::create(2023, 6, 1, 0, 0, 0)->format('Y-m-d H:i');
        $endDate = Carbon::create(2023, 6, 30, 23, 59, 59)->format('Y-m-d H:i');

        $policyDefinition = PolicyDefinition::from(
            PolicyDefinitionType::DATE_RANGE,
            '',
            null,
            Collection::make([$startDate, $endDate])
        );

        // Use reflection to access the protected method
        $reflectionMethod = new \ReflectionMethod(FeatureFlagStatusRepository::class, 'evaluateDateRange');
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invoke($repository, $policyDefinition);
        expect($result)->toBeTrue();

        // Test case 2: Current date is before the date range (should return false)
        $startDate = Carbon::create(2023, 7, 1, 0, 0, 0)->format('Y-m-d H:i');
        $endDate = Carbon::create(2023, 7, 31, 23, 59, 59)->format('Y-m-d H:i');

        $policyDefinition = PolicyDefinition::from(
            PolicyDefinitionType::DATE_RANGE,
            '',
            null,
            Collection::make([$startDate, $endDate])
        );

        $result = $reflectionMethod->invoke($repository, $policyDefinition);
        expect($result)->toBeFalse();

        // Test case 3: Current date is after the date range (should return false)
        $startDate = Carbon::create(2023, 5, 1, 0, 0, 0)->format('Y-m-d H:i');
        $endDate = Carbon::create(2023, 5, 31, 23, 59, 59)->format('Y-m-d H:i');

        $policyDefinition = PolicyDefinition::from(
            PolicyDefinitionType::DATE_RANGE,
            '',
            null,
            Collection::make([$startDate, $endDate])
        );

        $result = $reflectionMethod->invoke($repository, $policyDefinition);
        expect($result)->toBeFalse();

        // Test case 4: Invalid date range values (should return false)
        $policyDefinition = PolicyDefinition::from(
            PolicyDefinitionType::DATE_RANGE,
            '',
            null,
            Collection::make(['invalid-date'])
        );

        $result = $reflectionMethod->invoke($repository, $policyDefinition);
        expect($result)->toBeFalse();
    } finally {
        // Reset the test date
        Date::setTestNow();
    }
});
