# Using Feature Flags

Because Beacon is built as a driver for [Laravel Pennant](https://laravel.com/docs/pennant), the definition and usage of feature flags is the same as in Pennant, except you do not need to specify a closure to resolve the status. 

You define features in your application code, and then check if the feature is active.

If you specify a closure when defining the feature, it will be used in _addition_ to the remote policy if it is active.

## Defining a Feature Flag

Defining a feature flag is done using Pennant's `Feature::define()` method. With Beacon, all that is required is the name of the feature flag:

```php
Feature::define('test-flag');
```

If you want to override the remote policy, you can additionally specify a closure that returns a boolean value, or a variant value:

```php
Feature::define('example-feature', function () {
    // Your logic to determine if the feature is active
    return true; // or false, or a variant value
});
```

The closure will be executed when the remote policy is active, allowing you to add additional logic to determine if the feature should be considered active.

### Class-based Feature Flags

[Class-based flags](https://laravel.com/docs/pennant#class-based-features) are auto-discovered by Pennant and resolved automatically when you call `Feature::active()`. 
You can define a class-based feature flag by creating a class that has a `resolve()` method:

```php
class ExampleFeature
{
    public function resolve(mixed $scope): bool
    {
        return true; // or false, or a variant value
    }
}
```

Similar to passing in a closure to `Feature::define()`, if you define a class-based feature flag, it will be used in _addition_ to the remote policy if it is active.

When evaluating the flag in Beacon, the class name will be "sluggified" unless you specify a custom name in the class itself:

```php
class ExampleFeature
{
    public $name = 'example-feature';

    public function resolve(mixed $scope): bool
    {
        return true; // or false, or a variant value
    }
}
```

> [!NOTE]
> For usability, it is highly recommended that you add a custom name to your class-based feature flags.

## Evaluating a Feature Flag

To evaluate if a feature flag is active, you can use the `Feature::active()` method. This will return `true` if the feature is active, or `false` otherwise.

```php
if (Feature::active('example-feature')) {
    // Your code here
}
```

For class-based feature flags, you can also use `Feature::active()`, however, this time you will need to pass the class name as the first argument:

```php
if (Feature::active(ExampleFeature::class)) {
    // Your code here
}
```

### Context

Beacon automatically wraps the [scope](https://laravel.com/docs/pennant#scope) to add additional context for use in Beacon to determine the feature status.

The additional context always includes:

- Application Name (`BEACON_APP_NAME` or `APP_NAME`)
- Environment (`BEACON_APP_ENV` or `APP_ENV`)

In addition, the following are included if available:

- Session ID 
- IP Address
- User Agent
- Referrer
- Current URL
- HTTP Method

### Scope

The Beacon Pennant driver also provides a `BeaconScope` value object that can be used to pass arbitrary data as the scope when checking the feature status. 
This is useful for passing additional context that may be relevant to the feature flag evaluation.

```php
use Beacon\PennantDriver\BeaconScope;

Feature::for(BeaconScope::from([
    'highValueCustomer' => $user->isHighValue(),
    'betaOptIn' => $user->hasOptedInToBetaFeatures(),
]))->active('example-feature');
```

> [!WARNING]
> The `BeaconScope` will override the default scope of the current user, so you will need to pass the user explicitly if needed.




