<script setup>
// @ts-ignore
import { Code2 } from 'lucide-vue-next';
</script>
# Troubleshooting

This guide helps you diagnose and resolve common issues when using Beacon with your Laravel application.

## Common Issues

### Authentication and API Issues

#### Invalid API Token

**Symptoms:**
- 401 Unauthorized responses
- "Invalid API token" error messages
- Feature flags always returning inactive

**Solutions:**
1. Verify your API token in the `.env` file:
   ```dotenv
   BEACON_ACCESS_TOKEN=your_actual_token_here
   ```

2. Check that the token hasn't expired or been revoked in the Beacon dashboard

3. Ensure the token has the correct permissions for your application

4. Clear your configuration cache:
   ```bash
   php artisan config:clear
   ```

#### API Connection Issues

**Symptoms:**
- Timeout errors
- Connection refused errors
- Intermittent feature flag failures

**Solutions:**
1. Check your API URL configuration:
   ```dotenv
   BEACON_API_URL=https://beacon-hq.dev
   ```

2. Verify network connectivity:
   ```bash
   curl -I https://beacon-hq.dev/up
   ```

4. Check firewall and proxy settings

### Configuration Issues

#### Application or Environment Not Found

**Symptoms:**
- Feature flags not working in specific environments
- Inconsistent behavior across environments

**Solutions:**
1. Verify application name matches exactly:
   ```dotenv
   APP_NAME=my-exact-app-name
   # OR
   BEACON_APP_NAME=my-exact-app-name
   ```

2. Check environment name:
   ```dotenv
   APP_ENV=production
   # OR
   BEACON_ENVIRONMENT=production
   ```

#### Cache Issues

**Symptoms:**
- Stale feature flag values
- Changes not reflecting immediately
- Inconsistent behavior

**Solutions:**
1. Clear application cache:
   ```bash
   php artisan cache:clear
   ```

2. Clear Beacon-specific cache:
   ```bash
   php artisan cache:forget beacon:*
   ```

3. Adjust cache TTL for faster updates:
   ```dotenv
   BEACON_CACHE_TTL=300  # 5 minutes instead of 30
   ```

4. Disable caching temporarily for debugging:
   ```dotenv
   BEACON_CACHE_TTL=0
   ```

### Feature Flag Issues

#### Feature Flag Always Return Inactive

**Symptoms:**
- Feature flags never activate

**Solutions:**
1. Check if the feature flag exists in Beacon dashboard

2. Verify the feature flag is enabled for your environment

3. Check policy configuration and targeting rules

4. Test with a simple "always on" policy first

5. Verify context data is being sent correctly:
   ```php
   // Add debug logging
   Log::info('Feature flag context', [
       'flag' => 'my-flag',
       'context' => Feature::for($user)->context('my-flag'),
   ]);
   ```
   
6. Use Beacon's <kbd><Code2 /> Code</kbd> feature to create an API request to test with.

#### Inconsistent Feature Flag Behavior

**Symptoms:**
- Feature flags work sometimes but not others
- Different behavior for same user
- Random activation/deactivation

**Solutions:**
1. Verify policy logic and expressions

2. Check for caching issues (see Cache Issues section)

3. Review Rollout and Variants configuration

### Performance Issues

#### Slow Feature Flag Evaluation

**Symptoms:**
- High response times
- Timeouts during feature flag checks
- Application performance degradation

**Solutions:**
1. Enable and optimize caching:
   ```dotenv
   BEACON_CACHE_TTL=1800
   BEACON_CACHE_STORE=redis
   ```

#### High API Usage

**Symptoms:**
- Rate limiting errors (429 responses)
- High API costs
- Frequent cache misses

**Solutions:**
1. Increase cache TTL:
   ```dotenv
   BEACON_CACHE_TTL=3600  # 1 hour
   ```

2. Review and optimize policy complexity

## Getting Help

### Before Contacting Support

1. Check the [Beacon Status Page](https://status.beacon-hq.dev) for service issues
2. Review your configuration against this documentation
3. Test with a minimal reproduction case
4. Gather relevant logs and error messages

### Information to Include

When contacting support, include:

- Laravel version
- Beacon Pennant driver version
- Complete error messages and stack traces
- Relevant configuration (without sensitive data)
- Steps to reproduce the issue
- Expected vs actual behavior

### Community Resources

- [GitHub Issues](https://github.com/beacon-hq/pennant-driver/issues)
- [Discord Community](https://discord.gg/beacon-hq)
- [Documentation](https://docs.beacon-hq.dev)

## Monitoring and Alerting

### Set Up Monitoring

Monitor key metrics to catch issues early:

```php
// Monitor API response times
Log::info('Beacon API call', [
    'duration' => $duration,
    'flag' => $flagName,
    'result' => $result,
]);

// Monitor cache hit rates
Log::info('Beacon cache', [
    'hit' => $cacheHit,
    'flag' => $flagName,
]);
```

### Health Checks

Implement health checks for Beacon connectivity:

```php
// app/Http/Controllers/HealthController.php
public function beacon()
{
    try {
        $start = microtime(true);
        Feature::define('health-check');
        Feature::active('health-check');
        $duration = microtime(true) - $start;
        
        return response()->json([
            'status' => 'healthy',
            'response_time' => $duration,
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status' => 'unhealthy',
            'error' => $e->getMessage(),
        ], 503);
    }
}
```

### Alerting

Set up alerts for:
- High error rates
- Slow response times
- Rate limiting issues
- Cache miss rates

This helps you identify and resolve issues before they impact users.
