<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Organization;
use App\Models\StripePrice;
use App\Models\StripeProduct;
use App\Repositories\ProductRepository;
use App\Services\SubscriptionBillingService;
use App\Values\Organization as OrganizationValue;
use Brick\Money\Money;
use Illuminate\Support\Facades\Config;
use Mockery;
use Mockery\MockInterface;
use Stripe\StripeClient;

beforeEach(function () {
    // Set default config value for tests
    Config::set('beacon.billing.trial_fraud_limit', 20);
});

test('sets billing thresholds for subscription without product id', function () {
    // Create mock Stripe client
    $this->mock(StripeClient::class, function (MockInterface $mock) {
        $mock->subscriptions = Mockery::mock();
        $mock->subscriptions->shouldReceive('update')
            ->once()
            ->with('sub_123', [
                'billing_thresholds' => [
                    'amount_gte' => 2000, // $20 in cents
                ],
            ]);
    });

    // Create service with mocked repository and threshold amount
    $service = $this->mock(SubscriptionBillingService::class, function (MockInterface $mock) {
        $mock->makePartial();
        $mock->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive('getThresholdAmount')
            ->once()
            ->andReturn(Money::of(20, 'USD'));
    });

    // Use reflection to set the protected property
    $reflection = new \ReflectionClass($service);
    $property = $reflection->getProperty('productRepository');
    $property->setAccessible(true);
    $property->setValue($service, $this->mock(ProductRepository::class));

    // Test with trial subscription but no product ID
    $service->setFraudThreshold('sub_123', null, true);
});

test('sets billing thresholds with product id', function () {
    // Create mock product with entitlements
    $basePrice = new StripePrice();
    $basePrice->unit_amount = 1000; // $10

    $product = new StripeProduct();
    $product->id = 'prod_123';
    $product->entitlements = ['evaluations' => 100000];
    $product->basePrice = $basePrice;

    // Create mock Stripe client
    $this->mock(StripeClient::class, function (MockInterface $mock) {
        $mock->subscriptions = Mockery::mock();
        $mock->subscriptions->shouldReceive('update')
            ->once()
            ->with('sub_123', [
                'billing_thresholds' => [
                    'amount_gte' => 5000, // $50 in cents
                ],
            ]);
    });

    // Create service with mocked repository
    $productRepository = $this->mock(ProductRepository::class, function (MockInterface $mock) use ($product) {
        $mock->shouldReceive('find')
            ->with('prod_123')
            ->andReturn($product);
    });

    // Create a mock of the service with the getThresholdAmount method
    $service = $this->mock(SubscriptionBillingService::class, function (MockInterface $mock) {
        $mock->makePartial();
        $mock->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive('getThresholdAmount')
            ->once()
            ->andReturn(Money::of(50, 'USD'));
    });

    // Use reflection to set the protected property
    $reflection = new \ReflectionClass($service);
    $property = $reflection->getProperty('productRepository');
    $property->setAccessible(true);
    $property->setValue($service, $productRepository);

    // Test with trial subscription and product ID
    $service->setFraudThreshold('sub_123', 'prod_123', true);
});

it('skips reporting usage when billing is disabled', function () {
    // Arrange
    Config::set('beacon.billing.enabled', false);

    $service = app(SubscriptionBillingService::class);

    // Act
    $result = $service->reportUsage('org-123', OrganizationValue::from(id: 'org-123'));

    // Assert
    expect($result)->toBeFalse();
});

it('returns false when organization does not exist', function () {
    // Arrange
    Config::set('beacon.billing.enabled', true);

    $service = app(SubscriptionBillingService::class);

    // Act
    $result = $service->reportUsage('org-123', OrganizationValue::from(id: 'org-123'));

    // Assert
    expect($result)->toBeFalse();
});

it('returns false when organization has no active subscription', function () {
    // Arrange
    Config::set('beacon.billing.enabled', true);

    $organization = Organization::factory()->create();

    $service = app(SubscriptionBillingService::class);

    // Act
    $result = $service->reportUsage($organization->id, OrganizationValue::from(id: $organization->id));

    // Assert
    expect($result)->toBeFalse();
});

it('reports usage to Stripe for active subscription', function () {
    // Arrange
    Config::set('beacon.billing.enabled', true);

    $product = StripeProduct::create([
        'stripe_id' => 'prod_SBcYI2MMLp6qxl',
        'name' => 'Solo Plan',
        'description' =>  'Solo Plan',
        'active' => true,
        'entitlements' =>  ['evaluations' => 100000],
        'metadata' => [],
        'order' => 1,
    ]);

    $organization = Organization::factory()->create();
    $organization->subscriptions()->create([
        'type' => 'plan',
        'stripe_id' => 'sub_123',
        'stripe_status' => 'active',
    ]);

    // Mock the Stripe client
    $this->mock(StripeClient::class, function (MockInterface $mock) use ($organization) {
        $mock->shouldReceive('getService')
            ->with('billing')
            ->andReturnSelf();

        $mock->shouldReceive('getService')
            ->with('meterEvents')
            ->andReturnSelf();

        $mock->shouldReceive('create')
            ->once()
            ->with([
                'event_name' => 'flag_evaluations',
                'payload' => [
                    'evaluations' => 1,
                    'stripe_customer_id' => $organization->stripe_id,
                ],
                'identifier' => $organization->id,
            ])
            ->andReturn(true);
    });

    $service = app(SubscriptionBillingService::class);

    // Act
    $result = $service->reportUsage($organization->id, OrganizationValue::from(id: $organization->id));

    // Assert
    expect($result)->toBeTrue();
});

it('handles exceptions when reporting usage', function () {
    // Arrange
    Config::set('beacon.billing.enabled', true);

    $product = StripeProduct::create([
        'stripe_id' => 'prod_SBcYI2MMLp6qxl',
        'name' => 'Solo Plan',
        'description' =>  'Solo Plan',
        'active' => true,
        'entitlements' =>  ['evaluations' => 100000],
        'metadata' => [],
        'order' => 1,
    ]);

    $organization = Organization::factory()->create();
    $organization->subscriptions()->create([
        'type' => 'plan',
        'stripe_id' => 'sub_123',
        'stripe_status' => 'active',
    ]);

    // Mock the Stripe client
    $this->mock(StripeClient::class, function (MockInterface $mock) use ($organization) {
        $mock->shouldReceive('getService')
            ->with('billing')
            ->andReturnSelf();

        $mock->shouldReceive('getService')
            ->with('meterEvents')
            ->andReturnSelf();

        $mock->shouldReceive('create')
            ->once()
            ->with([
                'event_name' => 'flag_evaluations',
                'payload' => [
                    'evaluations' => 1,
                    'stripe_customer_id' => $organization->stripe_id,
                ],
                'identifier' => $organization->id,
            ])
            ->andThrow(new \Exception('Stripe API error'));
    });

    $service = app(SubscriptionBillingService::class);

    // Act
    $result = $service->reportUsage($organization->id, OrganizationValue::from(id: $organization->id));

    // Assert
    expect($result)->toBeFalse();
});
