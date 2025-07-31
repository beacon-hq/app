# Configuration

This guide covers all the configuration options available when using Beacon with your Laravel application.

## Environment Variables

Beacon uses several environment variables to configure its behavior. Add these to your `.env` file:

### Required Variables

```dotenv
# Pennant store configuration
PENNANT_STORE=beacon

# Beacon API access token
BEACON_ACCESS_TOKEN=your_api_token_here
```

### Optional Variables

```dotenv
# Application identification
BEACON_APP_NAME=my-laravel-app
BEACON_ENVIRONMENT=production

# API configuration
BEACON_API_URL=https://beacon-hq.dev
BEACON_API_PATH_PREFIX=/api

# Caching
BEACON_CACHE_TTL=1800
```

## Pennant Configuration

Configure the Beacon driver in your `config/pennant.php` file:

```php
<?php

return [
    'default' => env('PENNANT_STORE', 'beacon'),

    'stores' => [
        'array' => [
            'driver' => 'array',
        ],

        'database' => [
            'driver' => 'database',
            'connection' => env('PENNANT_CONNECTION'),
        ],

        'beacon' => [
            'driver' => 'beacon',
            'api_key' => env('BEACON_ACCESS_TOKEN'),
            'url' => env('BEACON_API_URL', 'https://beacon-hq.dev'),
            'path_prefix' => env('BEACON_API_PATH_PREFIX', '/api'),
            'app_name' => env('BEACON_APP_NAME', env('APP_NAME')),
            'environment' => env('BEACON_ENVIRONMENT', env('APP_ENV')),
            'cache_ttl' => env('BEACON_CACHE_TTL', 1800),
        ],
    ],
];
```

## Configuration Options

### API Configuration

#### `url`
- **Type**: String
- **Default**: `https://beacon-hq.dev`
- **Description**: The base URL for the Beacon API

#### `path_prefix`
- **Type**: String
- **Default**: `/api`
- **Description**: The path prefix for API endpoints

### Cache Configuration

#### `cache_ttl`
- **Type**: Integer
- **Default**: `1800` (30 minutes)
- **Description**: Time-to-live for cached feature flag results in seconds

### Application Configuration

#### `app_name`
- **Type**: String
- **Default**: Value of `BEACON_APP_NAME` or `APP_NAME`
- **Description**: The name of your application in Beacon

#### `environment`
- **Type**: String
- **Default**: Value of `BEACON_ENVIRONMENT` or `APP_ENV`
- **Description**: The environment name (e.g., production, staging, development)

## Security Considerations

### API Token Management

- Use different API tokens for different environments
- Rotate tokens regularly
- Store tokens securely, inject them using your application environment, and do not commit to revision control

## Troubleshooting Configuration

### Common Issues

**Invalid API token**: Ensure your `BEACON_ACCESS_TOKEN` is correct.

**Cache issues**: Clear your cache if you're seeing stale feature flag values:
```bash
php artisan cache:clear
```

**Environment mismatch**: Verify that `BEACON_APP_NAME` and `BEACON_ENVIRONMENT` match your Beacon dashboard configuration.

For more troubleshooting tips, see the [Troubleshooting](../troubleshooting) guide.
