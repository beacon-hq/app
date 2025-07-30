# Applications and Environments

Applications and Environments are fundamental concepts in Beacon that determine the policy used to evaluate a feature flags current state.

## Applications

An Application in Beacon represents a single Laravel project or service. 
Feature Flag configurations are tied to a specific environment for a given application.

> [!NOTE]
> Applications are team-scoped, meaning each team will have its own set of applications.
> Application names should be unique within a team.

### Application Configuration

When configuring your Laravel application to use Beacon, you specify the application name in your configuration:

```dotenv
BEACON_APP_NAME=my-laravel-app
```

## Environments

Environments allow you to have different feature flag configurations for different deployment stages of the same application.

### Common Environment Types

- **Local**: Local development environment
- **Testing**: Automated testing environment
- **Staging**: Pre-production testing environment
- **Production**: Live production environment

### Environment Configuration

Set the environment in your Laravel application configuration:

```dotenv
# .env
BEACON_ENVIRONMENT=production
```

## Working with Applications and Environments

### Creating Applications and Environments

Applications and Environments within Beacon are automatically created when your application makes its first request to Beacon with a new application or environment name. 
This makes it easy to add new applications and environments without manual configuration.

### Feature Flag Configuration

When evaluating feature flags, Beacon considers the team's feature flag configuration for the specific application-environment combination:

1. **Team-Level Feature Flags**: Each team owns their feature flags with one instance per flag
2. **Application-Environment Configuration**: Each flag can have specific configurations for different application-environment contexts

## Example Workflow

Here's a typical workflow for managing feature flags across applications and environments:

### 1. Development Phase
```php
// In your development environment
Feature::define('new-checkout-flow');

if (Feature::active('new-checkout-flow')) {
    // Test new checkout implementation
}
```

### 2. Staging Deployment
- Deploy code to staging environment
- Enable the feature flag in staging for testing
- Verify functionality with staging data

### 3. Production Rollout
- Deploy code to production environment
- Start with feature flag disabled or at low percentage
- Gradually increase rollout based on metrics and feedback
