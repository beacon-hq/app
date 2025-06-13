<?php

declare(strict_types=1);

use App\Http\Middleware\ApiRateLimitMiddleware;
use App\Services\OrganizationService;
use App\Values\Organization as OrganizationValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\RateLimiter;

test('middleware allows requests when billing is disabled', function () {
    // Arrange
    Config::set('beacon.billing.enabled', false);

    $middleware = new ApiRateLimitMiddleware();
    $request = new Request();

    $called = false;
    $next = function ($req) use (&$called) {
        $called = true;

        return response('OK');
    };

    // Act
    $middleware->handle($request, $next);

    // Assert
    expect($called)->toBeTrue('Next middleware should be called when billing is disabled');
});

test('middleware allows requests when rate limiting is disabled', function () {
    // Arrange
    Config::set('beacon.billing.enabled', true);
    Config::set('beacon.api.rate_limiting.enabled', false);

    $middleware = new ApiRateLimitMiddleware();
    $request = new Request();

    $called = false;
    $next = function ($req) use (&$called) {
        $called = true;

        return response('OK');
    };

    // Act
    $middleware->handle($request, $next);

    // Assert
    expect($called)->toBeTrue('Next middleware should be called when rate limiting is disabled');
});

test('middleware rate limits requests for trial accounts', function () {
    // Arrange
    Config::set('beacon.billing.enabled', true);
    Config::set('beacon.api.rate_limiting.enabled', true);

    // Create organization value object and mock organization service
    $organization = OrganizationValue::from('org-123', null, 'Test Organization');

    $organizationService = mock(OrganizationService::class);
    $organizationService->shouldReceive('hasActiveSubscription')->andReturn(true);
    $organizationService->shouldReceive('isTrialSubscription')->andReturn(true);

    app()->instance(OrganizationService::class, $organizationService);

    // Mock App::context()
    $context = (object) ['organization' => $organization];
    App::shouldReceive('context')->andReturn($context);

    // Mock the RateLimiter to simulate a limit of 2 requests per second for testing
    RateLimiter::shouldReceive('attempt')
        ->times(3)
        ->andReturnValues([true, true, false]);

    $middleware = new ApiRateLimitMiddleware();
    $request = new Request();

    $next = function ($req) {
        return response('OK');
    };

    // Act & Assert
    // First request should pass
    $response = $middleware->handle($request, $next);
    expect($response->getStatusCode())->toBe(200);

    // Second request should pass
    $response = $middleware->handle($request, $next);
    expect($response->getStatusCode())->toBe(200);

    // Third request should be rate limited
    $response = $middleware->handle($request, $next);
    expect($response->getStatusCode())->toBe(429);
    expect(json_decode($response->getContent(), true)['message'])->toContain('trial plan');
});

test('middleware rate limits requests for paid accounts', function () {
    // Arrange
    Config::set('beacon.billing.enabled', true);
    Config::set('beacon.api.rate_limiting.enabled', true);

    // Create organization value object and mock organization service
    $organization = OrganizationValue::from('org-456', null, 'Paid Organization');

    $organizationService = mock(OrganizationService::class);
    $organizationService->shouldReceive('hasActiveSubscription')->andReturn(true);
    $organizationService->shouldReceive('isTrialSubscription')->andReturn(false);

    app()->instance(OrganizationService::class, $organizationService);

    // Mock App::context()
    $context = (object) ['organization' => $organization];
    App::shouldReceive('context')->andReturn($context);

    // Mock the RateLimiter to simulate a limit of 3 requests per second for testing
    RateLimiter::shouldReceive('attempt')
        ->times(4)
        ->andReturnValues([true, true, true, false]);

    $middleware = new ApiRateLimitMiddleware();
    $request = new Request();

    $next = function ($req) {
        return response('OK');
    };

    // Act & Assert
    // First three requests should pass
    for ($i = 0; $i < 3; $i++) {
        $response = $middleware->handle($request, $next);
        expect($response->getStatusCode())->toBe(200);
    }

    // Fourth request should be rate limited
    $response = $middleware->handle($request, $next);
    expect($response->getStatusCode())->toBe(429);
    expect(json_decode($response->getContent(), true)['message'])->not->toContain('trial plan');
});
