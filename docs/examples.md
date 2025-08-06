# Usage Examples

This guide provides practical examples of how to use Beacon in real-world scenarios, covering common patterns and best practices.

## Basic Feature Flag Usage

### Simple Boolean Flag

The most basic use case is a simple on/off feature flag:

```php
// Define the feature flag
Feature::define('new-dashboard');

// Check if the feature is active
if (Feature::active('new-dashboard')) {
    return view('dashboard.new');
} else {
    return view('dashboard.legacy');
}
```

## User-Based Targeting

### Target Specific Users

Enable features for specific users during testing:

```php
// In your Beacon dashboard, create a policy:
// user_id = 123 OR user_id = 456

Feature::define('beta-features');

if (Feature::active('beta-features')) {
    // Show beta features to specific users
    $features = $this->getBetaFeatures();
} else {
    $features = $this->getStandardFeatures();
}
```

### Target by User Attributes

Target users based on their subscription tier:

```php
// In Beacon dashboard policy: subscription_tier = "premium"
Feature::define('premium-analytics');

if (Feature::active('premium-analytics')) {
    return view('analytics.premium');
}
```

## Percentage-Based Rollouts

### Gradual Feature Rollout

Roll out a feature to a percentage of users:

```php
// In Beacon dashboard, create a policy with percentage rollout
// Example: 25% of users based on user_id hash

Feature::define('new-checkout-flow');

if (Feature::active('new-checkout-flow')) {
    return $this->newCheckoutFlow($cart);
} else {
    return $this->legacyCheckoutFlow($cart);
}
```

### A/B Testing with Variants

Test different variants of a feature:

```php
Feature::define('homepage-layout');

$variant = Feature::value('homepage-layout');

return view('homepage.' . $variant);
```

## Advanced Targeting

### Custom Context Attributes

Use custom attributes for complex targeting:

```php
use Beacon\PennantDriver\BeaconScope;

// Target based on request context
$scope = BeaconScope::from([
    'user_id' => $user->id,
    'country' => $request->header('CF-IPCountry'),
    'device_type' => $this->detectDeviceType($request),
    'time_of_day' => now()->hour,
]);

Feature::define('regional-promotion');

if (Feature::for($scope)->active('regional-promotion')) {
    $promotion = $this->getRegionalPromotion($scope['country']);
    return view('promotions.regional', ['promotion' => $promotion]));
}
```

### Time-Based Features

Enable features during specific time periods:

```php
// In Beacon policy: current_hour >= 9 AND current_hour <= 17 (UTC)
Feature::define('business-hours-chat');

if (Feature::active('business-hours-chat')) {
    $this->enableLiveChat();
}
```

## Best Practices Summary

### 1. Use Descriptive Flag Names
```php
// Good
Feature::define('checkout-one-click-purchase');
Feature::define('dashboard-real-time-updates');

// Bad
Feature::define('feature1');
Feature::define('new-thing');
```

### 2. Clean Up Old Flags
```php
// Remove feature flags after full rollout
// Before cleanup:
if (Feature::active('new-checkout')) {
    return $this->newCheckout();
} else {
    return $this->oldCheckout();
}

// After cleanup:
return $this->newCheckout(); // Feature is now permanent
```

These examples demonstrate the flexibility and power of Beacon for managing feature flags in Laravel applications. Remember to always test both enabled and disabled states of your features.
