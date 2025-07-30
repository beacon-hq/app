# Context

Context is a superset of the Laravel Pennant [Scope](https://laravel.com/docs/pennant#scope). 
It includes the scope, as well as some default applications and request data.

## Context Structure

The Context structure is defined as follows:

```php
class FeatureFlagContext
{
    /**
     * @var class-string|typedef(array)|null 
     */
    public ?string $scopeType,
    public array $scope,
    public string $appName,     // APP_NAME
    public string $environment, // APP_ENV
    public ?string $sessionId = null,
    public ?string $ip = null,
    public ?string $userAgent = null,
    public ?string $referrer = null,
    public ?string $url = null,
    public ?string $method = null,
}
```
