<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\StripePrice;
use App\Models\StripeProduct;
use App\Repositories\ProductRepository;
use App\Services\SubscriptionBillingService;
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
